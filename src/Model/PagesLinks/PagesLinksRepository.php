<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Model\PagesLinks;

use NAttreid\Orm\Repository;
use Nextras\Dbal\DriverException;
use Nextras\Dbal\QueryException;
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
	 * @throws QueryException
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
	 * @param int $groupId
	 * @return ICollection|PageLink[]
	 */
	public function findByGroup(int $groupId): ICollection
	{
		return $this->findBy(['group' => $groupId]);
	}

	/**
	 * @param int|null $groupId
	 * @return ICollection|PageLink[]
	 */
	public function findVisible(int $groupId = null): ICollection
	{
		$result = $this->findAll()->findBy(['visible' => 1]);
		if ($groupId !== null) {
			$result = $result->findBy(['group' => $groupId]);
		}
		return $result;
	}

	/**
	 * Zmeni razeni
	 * @param int $id
	 * @param int|null $prevId
	 * @param int|null $nextId
	 * @throws QueryException
	 * @throws DriverException
	 */
	public function changeSort(int $id, ?int $prevId, ?int $nextId)
	{
		$this->mapper->changeSort('position', $id, $prevId, $nextId);
	}
}