<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Model\PagesLinks;

use NAttreid\ImageStorage\ImageStorage;
use NAttreid\WebManager\Model\PagesLinksGroups\PageLinkGroup;
use Nextras\Dbal\QueryException;
use Nextras\Orm\Entity\Entity;

/**
 * Class PageLink
 *
 * @property int $id {primary}
 * @property string $name
 * @property string $url
 * @property string|null $image
 * @property string|null $content
 * @property int|null $position
 * @property bool $openNewWindow {default false}
 * @property bool $visible {default 1}
 * @property PageLinkGroup $group {m:1 PageLinkGroup::$links}
 *
 * @author Attreid <attreid@gmail.com>
 */
class PageLink extends Entity
{
	/** @var ImageStorage */
	private $storage;

	public function injectStorage(ImageStorage $storage)
	{
		$this->storage = $storage;
	}

	protected function onBeforeRemove()
	{
		$this->storage->delete($this->image);
	}

	/**
	 * @throws QueryException
	 */
	protected function onBeforeInsert()
	{
		if (!isset($this->position)) {
			/* @var $repo PagesLinksRepository */
			$repo = $this->getRepository();
			$this->position = $repo->getMaxPosition() + 1;
		}
	}
}