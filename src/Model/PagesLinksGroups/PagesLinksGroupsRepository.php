<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Model\PagesLinksGroups;

use NAttreid\Orm\Repository;
use Nextras\Dbal\DriverException;
use Nextras\Dbal\QueryException;
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
	public static function getEntityClassNames(): array
	{
		return [PageLinkGroup::class];
	}

	/**
	 * Vrati nejvetsi pozici
	 * @return int
	 * @throws QueryException
	 */
	public function getMaxPosition(): int
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
	 * @param int|null $pageId
	 * @return ICollection|PageLinkGroup[]
	 */
	public function findVisible(int $pageId = null): ICollection
	{
		$result = $this->findAll()->findBy(['visible' => 1]);
		if ($pageId !== null) {
			$result = $result->findBy(['page' => $pageId]);
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

	/**
	 * @param int $pageId
	 * @return ICollection|PageLinkGroup[]
	 */
	public function findByPage(int $pageId): ICollection
	{
		return $this->findBy(['page' => $pageId]);
	}
}