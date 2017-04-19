<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Model\Pages;

use NAttreid\Cms\Model\Locale\Locale;
use NAttreid\Gallery\Control\Image;
use NAttreid\WebManager\Model\PagesGalleries\PageGallery;
use NAttreid\WebManager\Model\PagesViews\PageView;
use Nette\InvalidArgumentException;
use Nette\Utils\Strings;
use Nextras\Dbal\UniqueConstraintViolationException;
use Nextras\Orm\Entity\Entity;
use Nextras\Orm\Relationships\ManyHasMany;
use Nextras\Orm\Relationships\OneHasMany;

/**
 * Page
 *
 * @property int $id {primary}
 * @property string $name
 * @property string $url
 * @property string $completeUrl {virtual}
 * @property Page|null $parent {m:1 Page::$children}
 * @property OneHasMany|Page[] $children {1:m Page::$parent}
 * @property bool $hasChildren {virtual}
 * @property Locale $locale {m:1 Locale, oneSided=true}
 * @property string $title
 * @property string|null $image
 * @property string|null $keywords
 * @property string|null $description
 * @property string|null $content
 * @property ManyHasMany|PageView[] $views {m:n PageView::$pages, isMain=true}
 * @property int $position
 * @property OneHasMany|PageGallery[] $images {1:m PageGallery::$page, orderBy=position, cascade=[persist, remove]}
 *
 * @author Attreid <attreid@gmail.com>
 */
class Page extends Entity
{
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
	public function setUrl(string $url): void
	{
		if (!$this->locale) {
			throw new InvalidArgumentException('Locale must be set before calling setUrl');
		}
		if (Strings::match($url, '/[^A-Za-z0-9_-]/')) {
			throw new InvalidArgumentException('URL contains invalid characters');
		}

		/* @var $repository PagesRepository */
		$repository = $this->getRepository();
		$page = $repository->getByUrl($url, $this->locale->name);
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

	protected function onBeforeInsert(): void
	{
		if (!isset($this->position)) {
			/* @var $repo PagesRepository */
			$repo = $this->getRepository();
			$this->position = $repo->getMaxPosition() + 1;
		}
	}

	/**
	 * @return string
	 */
	protected function getterCompleteUrl(): string
	{
		$url = $this->url;
		if ($this->parent) {
			$url = $this->parent->completeUrl . '/' . $url;
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
}
