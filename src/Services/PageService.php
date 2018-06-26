<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Services;

use Kdyby\Translation\Translator;
use NAttreid\Cms\Configurator\Configurator;
use NAttreid\WebManager\IConfigurator;
use NAttreid\WebManager\Model\Content\Content;
use NAttreid\WebManager\Model\Orm;
use NAttreid\WebManager\Model\Pages\Page;
use Nette\Application\BadRequestException;
use Nette\Application\IRouter;
use Nette\Application\Routers\Route;
use Nette\Http\IResponse;
use Nette\SmartObject;
use Nextras\Dbal\UniqueConstraintViolationException;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Model\Model;

/**
 * Sluzba obsahu manageru
 *
 * @property-read string $defaultLink
 * @property-read string $pageLink
 * @property-read string $onePageLink
 *
 * @author Attreid <attreid@gmail.com>
 */
class PageService
{
	use SmartObject;

	/** @var Orm */
	private $orm;

	/** @var string */
	private $defaultLink;

	/** @var string */
	private $pageLink;

	/** @var string */
	private $onePageLink;

	/** @var string */
	private $module;

	/** @var Translator */
	private $translator;

	/** @var IConfigurator */
	private $configurator;

	public function __construct(string $defaultLink, string $pageLink, string $onePageLink, string $module, Model $orm, Translator $translator, Configurator $configurator)
	{
		$this->defaultLink = $defaultLink;
		$this->pageLink = $pageLink;
		$this->onePageLink = $onePageLink;
		$this->module = $module;
		$this->orm = $orm;
		$this->translator = $translator;
		$this->configurator = $configurator;
	}

	/**
	 * Vytvori routy
	 * @param IRouter $routes
	 * @param string $url
	 */
	public function createRoute(IRouter $routes, string $url): void
	{
		$this->createPageRoute($routes, $url);
		$this->createDefaultPageRoutes($routes, $url);
	}

	/**
	 * Vytvori routy stranek
	 * @param IRouter $routes
	 * @param string $url
	 */
	public function createPageRoute(IRouter $routes, string $url): void
	{
		list($presenter, $action) = explode(':', $this->pageLink);

		$routes[] = new Route($url . '[<url .*>]', [
			'presenter' => $presenter,
			'action' => $action,
			null => [
				Route::FILTER_IN => function ($params) {
					if ($this->orm->pages->exists($params['url'])) {
						return $params;
					}
					return null;
				}
			],
		]);
	}

	/**
	 * Vytvori routy defaultni stranky
	 * @param IRouter $routes
	 * @param string $url
	 */
	public function createDefaultPageRoutes(IRouter $routes, string $url): void
	{
		if ($this->configurator->onePage) {
			$routes[] = new Route($url, $this->onePageLink);
			$routes[] = new Route($url . 'index.php', $this->onePageLink, Route::ONE_WAY);
			$routes[] = new Route($url . '<presenter>[/<action>]', $this->defaultLink);

		} else {
			$routes[] = new Route($url, $this->defaultLink);
			$routes[] = new Route($url . 'index.php', $this->defaultLink, Route::ONE_WAY);
			$routes[] = new Route($url . '<presenter>[/<action>]', $this->defaultLink);
		}
	}

	/**
	 * Vrati stranku
	 * @param string $url
	 * @return Page
	 * @throws BadRequestException
	 */
	public function getPage(string $url = null): Page
	{
		$page = $this->orm->pages->getByUrl($url, $this->translator->getLocale());
		if (!$page || !$page->visible) {
			throw new BadRequestException(null, IResponse::S404_NOT_FOUND);
		}
		return $page;
	}

	/**
	 * @return string
	 */
	protected function getDefaultLink(): string
	{
		return ':' . $this->module . ':' . $this->defaultLink;
	}

	/**
	 * @return string
	 */
	protected function getPageLink(): string
	{
		return ':' . $this->module . ':' . $this->pageLink;
	}

	/**
	 * @return string
	 */
	protected function getOnePageLink(): string
	{
		return ':' . $this->module . ':' . $this->onePageLink;
	}

	/**
	 * Vrati stranky bez HP
	 * @return Page[]|ICollection
	 */
	public function findPages(): ICollection
	{
		return $this->orm->pages->findByLocale($this->translator->getLocale());
	}

	/**
	 * Vrati stranky v menu
	 * @return Page[]|ICollection
	 */
	public function findMenuPages(): ICollection
	{
		return $this->orm->pages->findMenu($this->translator->getLocale());
	}

	/**
	 * Vrati stranky pro onePage
	 * @return Page[]|ICollection
	 */
	public function findOnePages(): ICollection
	{
		return $this->orm->pages->findOnePage($this->translator->getLocale());
	}

	/**
	 * Vrati stranky v paticce
	 * @return Page[]|ICollection
	 */
	public function findFooterPages(): ICollection
	{
		return $this->orm->pages->findFooter($this->translator->getLocale());
	}

	/**
	 * Vrati text
	 * @param string $const
	 * @return Content
	 * @throws UniqueConstraintViolationException
	 */
	public function getContent(string $const): Content
	{
		$locale = $this->translator->getLocale();
		$content = $this->orm->content->getByConst($const, $locale);
		if (!$content) {
			$content = new Content;
			$this->orm->content->attach($content);

			$content->name = $const;
			$content->setLocale($locale);
			$content->setConst($const);
			$content->content = '';

			$this->orm->content->persistAndFlush($content);
		}
		return $content;
	}
}
