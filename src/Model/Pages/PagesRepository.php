<?php

namespace NAttreid\WebManager\Model;

use Nextras\Orm\Collection\ICollection;

/**
 * Pages Repository
 *
 * @author Attreid <attreid@gmail.com>
 */
class PagesRepository extends \NAttreid\Orm\Repository
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
	 * Vrati kolekci
	 * @return ICollection|Page
	 */
	public function findAll()
	{
		return parent::findAll()->orderBy('position');
	}

	/**
	 * Vrati stranky (mimo hlavni, pokud existuje)
	 * @return ICollection|Page
	 */
	public function findPages()
	{
		return $this->findBy(['url!=' => ''])
			->orderBy('position');
	}

	/**
	 * Vrati stranku podle url
	 * @param string $url
	 * @return Page
	 */
	public function getByUrl($url)
	{
		return $this->getBy(['url' => $url]);
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
