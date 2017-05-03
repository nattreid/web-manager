<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Model\PagesLinksGroups;

use NAttreid\WebManager\Model\Pages\Page;
use NAttreid\WebManager\Model\PagesLinks\PageLink;
use Nextras\Orm\Entity\Entity;
use Nextras\Orm\Relationships\OneHasMany;

/**
 * Class PageLinkGroup
 *
 * @property int $id {primary}
 * @property string $name
 * @property Page $page {m:1 Page::$linkGroups}
 * @property OneHasMany|PageLink[] $links {1:m PageLink::$group, orderBy=position, cascade=[persist, remove]}
 * @property int|null $position
 * @property bool $visible {default 1}
 * @property int $quantity
 *
 * @author Attreid <attreid@gmail.com>
 */
class PageLinkGroup extends Entity
{
	protected function onBeforeInsert()
	{
		if (!isset($this->position)) {
			/* @var $repo PagesLinksGroupsRepository */
			$repo = $this->getRepository();
			$this->position = $repo->getMaxPosition() + 1;
		}
	}
}