<?php

declare(strict_types = 1);

namespace NAttreid\WebManager\Model\PagesViews;

use NAttreid\Orm\Repository;
use Nextras\Orm\Collection\ICollection;

/**
 * PagesViews Repository
 *
 * @method ICollection|PageView[] findAll()
 *
 * @author Attreid <attreid@gmail.com>
 */
class PagesViewsRepository extends Repository
{

	/** @var PagesViewsMapper */
	protected $mapper;

	public static function getEntityClassNames()
	{
		return [PageView::class];
	}

	public function fetchPairsByName(): array
	{
		$arr = $this->fetchPairsById();
		asort($arr);
		return $arr;
	}


	public function fetchPairsById(): array
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
	public function fetchUntranslatedPairsById(): array
	{
		$arr = [];
		$rows = $this->findAll();
		foreach ($rows as $row) {
			$arr[$row->id] = $row->untranslatedName;
		}
		return $arr;
	}
}
