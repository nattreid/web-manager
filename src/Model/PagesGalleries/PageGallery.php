<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Model\PagesGalleries;

use NAttreid\ImageStorage\ImageStorage;
use NAttreid\WebManager\Model\Pages\Page;
use Nextras\Dbal\QueryException;
use Nextras\Orm\Entity\Entity;

/**
 * Class PageGallery
 *
 * @property int $id {primary}
 * @property Page $page {m:1 Page::$images}
 * @property string $name
 * @property int $position
 *
 * @author Attreid <attreid@gmail.com>
 */
class PageGallery extends Entity
{
	/** @var ImageStorage */
	private $storage;

	public function injectStorage(ImageStorage $storage): void
	{
		$this->storage = $storage;
	}

	protected function onBeforeRemove(): void
	{
		$this->storage->delete($this->name);
	}

	/**
	 * @throws QueryException
	 */
	protected function onBeforeInsert(): void
	{
		if (!isset($this->position)) {
			/* @var $repo PagesGalleriesRepository */
			$repo = $this->getRepository();
			$this->position = $repo->getMaxPosition($this->page->id) + 1;
		}
	}

	function __toString()
	{
		return $this->name;
	}
}