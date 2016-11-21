<?php

namespace NAttreid\WebManager\Model;

use NAttreid\Crm\Model\LocalesMapper;
use NAttreid\Orm\Structure\Table;
use Nette\Caching\Cache;
use Nextras\Dbal\QueryBuilder\QueryBuilder;

/**
 * Pages Mapper
 *
 * @author Attreid <attreid@gmail.com>
 */
class PagesMapper extends Mapper
{

	private $tag = 'netta/pages';

	protected function createTable(Table $table)
	{
		$table->addPrimaryKey('id')
			->int()
			->setAutoIncrement();
		$table->addColumn('name')
			->varChar(100);
		$table->addColumn('url')
			->varChar(100);
		$table->addForeignKey('localeId', LocalesMapper::class);
		$table->addColumn('title')
			->varChar(150);
		$table->addColumn('image')
			->varChar(150)
			->setDefault(null);
		$table->addColumn('keywords')
			->varChar()
			->setDefault(null);
		$table->addColumn('description')
			->varChar()
			->setDefault(null);
		$table->addColumn('content')
			->text()
			->setDefault(null);
		$table->addColumn('group')
			->int()
			->setDefault(0)
			->setKey();
		$table->addColumn('position')
			->int()
			->setKey();
		$table->addUnique('url', 'localeId');
	}

	/**
	 * Smaze cache
	 */
	public function cleanCache()
	{
		$this->cache->clean([
			Cache::TAGS => [$this->tag]
		]);
	}

	/**
	 * Je URL v databazi
	 * @param string $url
	 * @return boolean
	 */
	public function exists($url)
	{
		$key = 'pagesList';
		$rows = $this->cache->load($key);
		if ($rows === null) {
			$rows = $this->cache->save($key, function () {
				$result = [];
				foreach ($this->getRepository()->findAll() as $page) {
					/* @var $page Page */
					$result[$page->url] = true;
				}
				return $result;
			}, [
				Cache::TAGS => [$this->tag]
			]);
		}
		if (isset($rows[$url])) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @param $group
	 * @param $locale
	 * @return QueryBuilder
	 */
	public function findByGroup($group, $locale)
	{
		return $this->builder()
			->andWhere('group & %i > 0', $group)
			->andWhere('locale = %s', $locale);
	}

	/**
	 * Vrati nejvetsi pozici
	 * @return int
	 */
	public function getMaxPosition()
	{
		return $this->connection->query('SELECT IFnull(MAX([position]), 0) position FROM %table', $this->getTableName())->fetch()->position;
	}

}
