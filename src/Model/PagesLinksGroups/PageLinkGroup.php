<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Model\PagesLinksGroups;

use NAttreid\WebManager\Model\Orm;
use NAttreid\WebManager\Model\Pages\Page;
use NAttreid\WebManager\Model\PagesLinks\PageLink;
use Nextras\Dbal\QueryException;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\Entity;
use Nextras\Orm\Relationships\OneHasMany;

/**
 * Class PageLinkGroup
 *
 * @property int $id {primary}
 * @property string $name
 * @property Page $page {m:1 Page::$linkGroups}
 * @property OneHasMany|PageLink[] $links {1:m PageLink::$group, orderBy=position, cascade=[persist, remove]}
 * @property PageLink[] $visibleLinks {virtual}
 * @property int|null $position
 * @property bool $visible {default 1}
 * @property int $quantity
 * @property bool $hasVisibleLinks {virtual}
 *
 * @author Attreid <attreid@gmail.com>
 */
class PageLinkGroup extends Entity
{
	/**
	 * @throws QueryException
	 */
	protected function onBeforeInsert()
	{
		if (!isset($this->position)) {
			/* @var $repo PagesLinksGroupsRepository */
			$repo = $this->getRepository();
			$this->position = $repo->getMaxPosition() + 1;
		}
	}

	/**
	 * @return ICollection|PageLink[]
	 */
	protected function getterVisibleLinks(): ICollection
	{
		/* @var $orm Orm */
		$orm = $this->getModel();
		return $orm->pagesLinks->findVisible($this->id);
	}

	protected function getterHasVisibleLinks(): bool
	{
		return count($this->visibleLinks) > 0;
	}
}