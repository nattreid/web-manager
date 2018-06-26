<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Model\Pages;

use NAttreid\Orm\Repository;
use NAttreid\WebManager\Model\PagesViews\PagesViewsMapper;
use Nextras\Dbal\DriverException;
use Nextras\Dbal\QueryException;
use Nextras\Orm\Collection\ICollection;

/**
 * Pages Repository
 *
 * @method Page getByUrl(?string $url, string $locale) Vrati stranku podle url
 * @method Page getById($id)
 * @method ICollection|Page[] findByLocale(string $locale): ICollection
 * @method void cleanCache() Smaze cache
 * @method bool exists(string $url = null) Je URL v databazi
 *
 * @author Attreid <attreid@gmail.com>
 */
class PagesRepository extends Repository
{

	/** @var PagesMapper */
	protected $mapper;

	protected function init(): void
	{
		$this->onFlush[] = function ($persisted, $removed) {
			if (!empty($persisted) || !empty($removed)) {
				$this->cleanCache();
			}
		};
	}

	public static function getEntityClassNames(): array
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
	 * @return ICollection|Page[]
	 */
	public function findVisible(): ICollection
	{
		return $this->findAll()->findBy(['visible' => 1]);
	}

	/**
	 * Vrati hlavni stranky
	 * @param bool $onlyVisible
	 * @return ICollection|Page[]
	 */
	public function findMain(bool $onlyVisible = false): ICollection
	{
		$result = ($onlyVisible ? $this->findVisible() : $this->findAll())
			->findBy(['parent' => null]);
		return $result;
	}

	/**
	 * Vrati stranky v menu
	 * @param $locale
	 * @return Page[]|ICollection
	 */
	public function findMenu(string $locale): ICollection
	{
		return $this->findMain(true)
			->findBy([
				'this->views->id' => PagesViewsMapper::MENU,
				'this->locale->name' => $locale
			]);
	}

	/**
	 * Vrati stranky pro onePage
	 * @param $locale
	 * @return Page[]|ICollection
	 */
	public function findOnePage(string $locale): ICollection
	{
		return $this->findMenu($locale)
			->findBy(['isLink' => 0]);
	}

	/**
	 * Vrati stranky v paticce
	 * @param $locale
	 * @return Page[]|ICollection
	 */
	public function findFooter(string $locale): ICollection
	{
		return $this->findMain(true)
			->findBy([
				'this->views->id' => PagesViewsMapper::FOOTER,
				'this->locale->name' => $locale
			]);
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
	 * Vrati nejmensi pozici
	 * @return int
	 * @throws QueryException
	 */
	public function getMinPosition(): int
	{
		return $this->mapper->getMin('position');
	}

	/**
	 * Zmeni razeni
	 * @param int $id
	 * @param int|null $prevId
	 * @param int|null $nextId
	 * @param string $locale
	 * @throws QueryException
	 * @throws DriverException
	 */
	public function changeSort(int $id, ?int $prevId, ?int $nextId, string $locale): void
	{
		$this->mapper->changeSort('position', $id, $prevId, $nextId);
		$main = $this->getByUrl(null, $locale);
		if ($main) {
			$main->position = 0;
			$this->persistAndFlush($main);
		}
	}
}
