<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Model\PagesLinksGroups;

use NAttreid\Orm\Repository;
use Nextras\Orm\Collection\ICollection;

/**
 * Class PagesLinksGroupsRepository
 *
 * @method PageLinkGroup getById($id)
 *
 * @author Attreid <attreid@gmail.com>
 */
class PagesLinksGroupsRepository extends Repository
{
	/** @var PagesLinksGroupsMapper */
	protected $mapper;

	/**
	 * Returns possible entity class names for current repository.
	 * @return string[]
	 */
	public static function getEntityClassNames()
	{
		return [PageLinkGroup::class];
	}

	/**
	 * Vrati nejvetsi pozici
	 * @return int
	 */
	public function getMaxPosition()
	{
		return $this->mapper->getMax('position');
	}

	/**
	 * @return ICollection|PageLinkGroup[]
	 */
	public function findAll(): ICollection
	{
		return parent::findAll()->orderBy('position');
	}

	/**
	 * @return ICollection|PageLinkGroup[]
	 */
	public function findVisible(): ICollection
	{
		return $this->findAll()->findBy(['visible' => 1]);
	}

	/**
	 * Zmeni razeni
	 * @param int $id
	 * @param int $prevId
	 * @param int $nextId
	 */
	public function changeSort($id, $prevId, $nextId)
	{
		$this->mapper->changeSort('position', $id, $prevId, $nextId);
	}
}