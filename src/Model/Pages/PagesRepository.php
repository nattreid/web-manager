<?php

declare(strict_types = 1);

namespace NAttreid\WebManager\Model\Pages;

use NAttreid\Orm\Repository;
use NAttreid\WebManager\Model\PagesViews\PagesViewsMapper;
use Nextras\Orm\Collection\ICollection;

/**
 * Pages Repository
 *
 * @method Page getByUrl($url, $locale) Vrati stranku podle url
 * @method Page getById($id)
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
	public function findAll(): ICollection
	{
		return parent::findAll()->orderBy('position');
	}

	/**
	 * Vrati hlavni stranky
	 * @return ICollection|Page[]
	 */
	public function findMain(): ICollection
	{
		return $this->findAll()->findBy(['parent' => null]);
	}


	/**
	 * Vrati lokalizovane stranky bez HP
	 * @param string $locale
	 * @return Page[]|ICollection
	 */
	public function findByLocale(string $locale): ICollection
	{
		return $this->findAll()
			->findBy([
				'url!=' => '',
				'this->locale->name' => $locale
			]);
	}

	/**
	 * Vrati stranky v menu
	 * @param $locale
	 * @return Page[]|ICollection
	 */
	public function findMenu(string $locale): ICollection
	{
		return $this->findMain()
			->findBy([
				'this->views->id' => PagesViewsMapper::MENU,
				'this->locale->name' => $locale
			]);
	}

	/**
	 * Vrati stranky v paticce
	 * @param $locale
	 * @return Page[]|ICollection
	 */
	public function findFooter(string $locale): ICollection
	{
		return $this->findAll()
			->findBy([
				'this->views->id' => PagesViewsMapper::FOOTER,
				'this->locale->name' => $locale
			]);
	}

	/**
	 * Je URL v databazi
	 * @param string $url
	 * @return bool
	 */
	public function exists(string $url = null): bool
	{
		return $this->mapper->exists($url);
	}

	/**
	 * Vrati nejvetsi pozici
	 * @return int
	 */
	public function getMaxPosition(): int
	{
		return $this->mapper->getMaxPosition('position');
	}

	/**
	 * Zmeni razeni
	 * @param int $id
	 * @param int $prevId
	 * @param int $nextId
	 */
	public function changeSort(int $id, int $prevId, int $nextId)
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
