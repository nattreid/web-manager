<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Model\PagesGalleries;

use NAttreid\WebManager\Model\Pages\Page;
use Nextras\Orm\Entity\Entity;
use WebChemistry\Images\AbstractStorage;

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
	/** @var AbstractStorage */
	private $storage;

	public function injectStorage(AbstractStorage $storage)
	{
		$this->storage = $storage;
	}

	protected function onBeforeRemove()
	{
		$this->storage->delete($this->name);
	}

	protected function onBeforeInsert()
	{
		if (!isset($this->position)) {
			/* @var $repo PagesGalleriesRepository */
			$repo = $this->getRepository();
			$this->position = $repo->getMaxPosition($this->page) + 1;
		}
	}

	function __toString()
	{
		return $this->name;
	}
}