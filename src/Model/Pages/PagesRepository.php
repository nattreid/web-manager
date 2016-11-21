<?php

namespace NAttreid\WebManager\Model;

use NAttreid\Orm\Repository;
use Nextras\Orm\Collection\ICollection;

/**
 * Pages Repository
 *
 * @author Attreid <attreid@gmail.com>
 */
class PagesRepository extends Repository
{

	/** @var PagesMapper */
	protected $mapper;

	protected function init()
	{
		$this->onFlush[] = function ($persisted, $removed) {
			if (!empty($persisted) || !empty($removed)) {
				$this->cleanCache();
			}
		};
	}

	public static function getEntityClassNames()
	{
		return [Page::class];
	}

	/**
	 * @return ICollection|Page[]
	 */
	public function findAll()
	{
		return parent::findAll()->orderBy('position');
	}


	/**
	 * Vrati lokalizovane stranky bez HP
	 * @return ICollection|Page[]
	 */
	public function findByLocale($locale)
	{
		return $this->findAll()
			->findBy([
				'url!=' => '',
				'this->locale->name' => $locale
			]);
	}

	/**
	 * Vrati stranku podle url
	 * @param string $url
	 * @param string $locale
	 * @return Page
	 */
	public function getByUrl($url, $locale)
	{
		return $this->getBy([
			'url' => $url,
			'this->locale->name' => $locale
		]);
	}

	/**
	 * Vrati stranky v menu
	 * @param $locale
	 * @return Page[]|ICollection
	 */
	public function findMenu($locale)
	{
		return $this->findAll()
			->findBy([
				'this->groups->id' => 1,
				'this->locale->name' => $locale
			]);
	}

	/**
	 * Vrati stranky v paticce
	 * @param $locale
	 * @return Page[]|ICollection
	 */
	public function findFooter($locale)
	{
		return $this->findAll()
			->findBy([
				'this->groups->id' => 2,
				'this->locale->name' => $locale
			]);
	}

	/**
	 * Je URL v databazi
	 * @param string $url
	 * @return boolean
	 */
	public function exists($url)
	{
		return $this->mapper->exists($url);
	}

	/**
	 * Vrati nejvetsi pozici
	 * @return int
	 */
	public function getMaxPosition()
	{
		return $this->mapper->getMaxPosition();
	}

	/**
	 * Zmeni razeni
	 * @param mixed $id
	 * @param mixed $prevId
	 * @param mixed $nextId
	 */
	public function changeSort($id, $prevId, $nextId)
	{
		$this->mapper->changeSort('position', $id, $prevId, $nextId);
	}

	/**
	 * Smaze cache
	 */
	public function cleanCache()
	{
		$this->mapper->cleanCache();
	}

}
