<?php

namespace NAttreid\WebManager\Model;

use NAttreid\Orm\Repository;
use Nextras\Orm\Collection\ICollection;

/**
 * PagesGroups Repository
 *
 * @method ICollection|PageGroup[] findAll()
 *
 * @author Attreid <attreid@gmail.com>
 */
class PagesGroupsRepository extends Repository
{

	/** @var PagesMapper */
	protected $mapper;

	public static function getEntityClassNames()
	{
		return [PageGroup::class];
	}

	public function fetchPairsByName()
	{
		$arr = $this->fetchPairsById();
		asort($arr);
		return $arr;
	}


	public function fetchPairsById()
	{
		$arr = [];
		$rows = $this->findAll();
		foreach ($rows as $row) {
			$arr[$row->id] = $row->translatedName;
		}
		return $arr;
	}

	/**
	 * @return array
	 */
	public function fetchUntranslatedPairsById()
	{
		$arr = [];
		$rows = $this->findAll();
		foreach ($rows as $row) {
			$arr[$row->id] = $row->untranslatedName;
		}
		return $arr;
	}
}
