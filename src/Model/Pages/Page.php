<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Model\Pages;

use NAttreid\Cms\Model\Locale\Locale;
use NAttreid\Form\Control\Spectrum\Color;
use NAttreid\Gallery\Control\Image;
use NAttreid\Routing\RouterFactory;
use NAttreid\WebManager\Model\Orm;
use NAttreid\WebManager\Model\PagesGalleries\PageGallery;
use NAttreid\WebManager\Model\PagesLinksGroups\PageLinkGroup;
use NAttreid\WebManager\Model\PagesViews\PagesViewsMapper;
use NAttreid\WebManager\Model\PagesViews\PageView;
use NAttreid\WebManager\Services\PageService;
use Nette\Application\Application;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\Http\Request;
use Nette\Http\Url;
use Nette\Http\UrlScript;
use Nette\InvalidArgumentException;
use Nette\Utils\Strings;
use Nextras\Dbal\QueryException;
use Nextras\Dbal\UniqueConstraintViolationException;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\Entity;
use Nextras\Orm\Relationships\ManyHasMany;
use Nextras\Orm\Relationships\OneHasMany;
use Symfony\Component\Debug\Debug;
use Tracy\Debugger;

/**
 * Page
 *
 * @property int $id {primary}
 * @property string $name
 * @property string|null $url
 * @property string|null $completeUrl {virtual}
 * @property string $link {virtual}
 * @property string $absoluteLink {virtual}
 * @property bool $isLink {default 0}
 * @property bool $isHomePage {virtual}
 * @property Page|null $parent {m:1 Page::$children}
 * @property OneHasMany|Page[] $children {1:m Page::$parent, orderBy=[position=ASC]}
 * @property bool $hasChildren {virtual}
 * @property ICollection|Page[] $menuChildren {virtual}
 * @property bool $hasMenuChildren {virtual}
 * @property ICollection|Page[] $footerChildren {virtual}
 * @property bool $hasFooterChildren {virtual}
 * @property Locale $locale {m:1 Locale, oneSided=true}
 * @property string|null $title
 * @property bool $showTitle {default true}
 * @property string|null $image
 * @property string|null $background
 * @property Color|null $backgroundColor {virtual}
 * @property string|null $keywords
 * @property string|null $description
 * @property string|null $content
 * @property ManyHasMany|PageView[] $views {m:m PageView::$pages, isMain=true}
 * @property int $position
 * @property bool $visible {default 1}
 * @property OneHasMany|PageGallery[] $images {1:m PageGallery::$page, orderBy=position, cascade=[persist, remove]}
 * @property OneHasMany|PageLinkGroup[] $linkGroups {1:m PageLinkGroup::$page, orderBy=position, cascade=[persist, remove]}
 * @property PageLinkGroup[] $visibleLinkGroups {virtual}
 *
 * @author Attreid <attreid@gmail.com>
 */
class Page extends Entity
{

	/** @var PageService */
	private $pageService;

	/** @var RouterFactory */
	private $routerFactory;

	/** @var Application */
	private $application;

	public function injectPageServices(PageService $pageService, RouterFactory $routerFactory, Application $application)
	{
		$this->pageService = $pageService;
		$this->routerFactory = $routerFactory;
		$this->application = $application;
	}

	/**
	 * Vrati nazvy zobarazeni
	 * @return string[]
	 */
	public function getViews(): array
	{
		$result = [];
		foreach ($this->views->get() as $row) {
			/* @var $row PageView */
			$result[] = $row->translatedName;
		}
		return $result;
	}

	/**
	 * Nastavi URL
	 * @param string $url
	 * @throws InvalidArgumentException
	 * @throws UniqueConstraintViolationException
	 */
	public function setUrl(?string $url): void
	{
		if ($this->locale === null) {
			throw new InvalidArgumentException('Locale must be set before calling setUrl');
		}
		if ($this->isLink === null) {
			throw new InvalidArgumentException("'isLink' must be set before calling setUrl");
		}
		if (!$this->isLink && Strings::match($url, '/[^A-Za-z0-9_-]/')) {
			throw new InvalidArgumentException('URL contains invalid characters');
		}

		$completeUrl = !$this->isLink && $this->parent && $url !== null ?
			$this->parent->completeUrl . '/' . $url
			: $url;

		/* @var $repository PagesRepository */
		$repository = $this->getRepository();
		$page = $repository->getByUrl($completeUrl, $this->locale->name);
		if ($page !== null && $page !== $this) {
			throw new UniqueConstraintViolationException("Page with '$url' exists");
		}
		$this->url = $url;
	}

	/**
	 * @param Image[] $images
	 */
	public function addImages(array $images): void
	{
		$counter = 1;
		foreach ($images as $image) {
			$pageGallery = new PageGallery;
			$pageGallery->name = $image->name;
			$pageGallery->position = $counter++;
			$this->images->add($pageGallery);
		}
	}

	/**
	 * @throws QueryException
	 */
	public function onBeforeInsert(): void
	{
		/* @var $repo PagesRepository */
		$repo = $this->getRepository();
		if ($this->isHomePage) {
			$this->position = $repo->getMinPosition() - 1;
		} elseif (!isset($this->position)) {
			$this->position = $repo->getMaxPosition() + 1;
		}
	}

	/**
	 * @throws QueryException
	 */
	public function onBeforeUpdate()
	{
		/* @var $repo PagesRepository */
		$repo = $this->getRepository();
		if ($this->isHomePage) {
			$this->position = $repo->getMinPosition() - 1;
		}
	}

	/**
	 * @param bool|null $fromChildren
	 * @return string|null
	 */
	protected function getterCompleteUrl(?bool $fromChildren = false): ?string
	{
		$url = $fromChildren ? Strings::webalize($this->url) : $this->url;
		if (!$this->isLink && $this->parent) {
			$url = $this->parent->getterCompleteUrl(true) . '/' . $url;
		}
		return $url;
	}

	/**
	 * @return string
	 * @throws InvalidLinkException
	 */
	protected function getterLink(): string
	{
		/* @var $presenter Presenter */
		$presenter = $this->application->getPresenter();
		$url = $this->completeUrl;

		if (!$this->isLink) {
			$url = $presenter->link($this->pageService->pageLink, [
				'url' => $url,
				$this->routerFactory->variable => $this->locale->name
			]);
		} else {
			$httpRequest = $this->createRequest($url);
			$request = $this->application->getRouter()->match($httpRequest);
			if ($request) {
				try {
					$iMode = $presenter->invalidLinkMode;
					$presenter->invalidLinkMode = $presenter::INVALID_LINK_EXCEPTION;
					$url = $presenter->link(':' . $request->presenterName . ':' . $request->parameters['action'], $request->parameters);
					$presenter->invalidLinkMode = $iMode;
				} catch (InvalidLinkException $ex) {
				}
			}
		}
		return $url;
	}

	private function createRequest(string $url): Request
	{
		/* @var $presenter Presenter */
		$presenter = $this->application->getPresenter();
		$u = $presenter->getHttpRequest()->getUrl();
		$urlScript = new UrlScript($url);
		$urlScript->host = $urlScript->host ?: $u->host;
		$urlScript->port = $urlScript->port ?: $u->port;
		$urlScript->scheme = $urlScript->scheme ?: $u->scheme;

		return new Request($urlScript);
	}

	/**
	 * @return string
	 * @throws InvalidLinkException
	 */
	protected function getterAbsoluteLink(): string
	{
		/* @var $presenter Presenter */
		$presenter = $this->application->getPresenter();

		$url = $this->completeUrl;
		if ($this->isLink) {
			$u = new Url($url);
			if (!$u->host) {
				$url = $presenter->link('//' . $this->pageService->defaultLink) . $url;
			}
		} else {
			$url = $presenter->link('//' . $this->pageService->pageLink, [
				'url' => $url,
				$this->routerFactory->variable => $this->locale->name
			]);
		}
		return $url;
	}

	/**
	 * @return bool
	 */
	protected function getterHasChildren(): bool
	{
		return !empty($this->children->count());
	}

	/**
	 * @return ICollection|Page[]
	 */
	protected function getterMenuChildren(): ICollection
	{
		/* @var $repository PagesRepository */
		$repository = $this->getRepository();
		return $repository->findBy([
			'parent' => $this->id,
			'this->views->id' => PagesViewsMapper::MENU,
		])
			->orderBy('position');
	}

	/**
	 * @return bool
	 */
	protected function getterHasMenuChildren(): bool
	{
		return !empty($this->menuChildren->count());
	}

	/**
	 * @return ICollection|Page[]
	 */
	protected function getterFooterChildren(): ICollection
	{
		/* @var $repository PagesRepository */
		$repository = $this->getRepository();
		return $repository->findBy([
			'parent' => $this->id,
			'this->views->id' => PagesViewsMapper::FOOTER,
		])
			->orderBy('position');
	}

	/**
	 * @return bool
	 */
	protected function getterHasFooterChildren(): bool
	{
		return !empty($this->footerChildren->count());
	}

	/**
	 * @return bool
	 */
	protected function getterIsHomePage(): bool
	{
		return $this->url === null;
	}

	/**
	 * @return ICollection|PageLinkGroup[]
	 */
	protected function getterVisibleLinkGroups(): ICollection
	{
		/* @var $orm Orm */
		$orm = $this->getRepository()->getModel();
		return $orm->pagesLinksGroups->findVisible($this->id);
	}

	/**
	 * @return Color|null
	 */
	protected function getterBackgroundColor(): ?Color
	{
		if ($this->background === null) {
			return null;
		}
		return new Color($this->background);
	}
}
