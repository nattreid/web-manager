<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Model\PagesLinks;

use NAttreid\Orm\Repository;
use Nextras\Orm\Collection\ICollection;

/**
 * Class PagesLinksRepository
 *
 * @method PageLink getById($id)
 *
 * @author Attreid <attreid@gmail.com>
 */
class PagesLinksRepository extends Repository
{
	/** @var PagesLinksMapper */
	protected $mapper;

	/**
	 * Returns possible entity class names for current repository.
	 * @return string[]
	 */
	public static function getEntityClassNames()
	{
		return [PageLink::class];
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
	 * @return ICollection|PageLink[]
	 */
	public function findAll(): ICollection
	{
		return parent::findAll()->orderBy('position');
	}

	/**
	 * @return ICollection|PageLink[]
	 */
	public function findByGroup(int $groupId): ICollection
	{
		return $this->findBy(['group' => $groupId]);
	}

	/**
	 * @return ICollection|PageLink[]
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