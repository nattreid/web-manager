<?php

namespace NAttreid\WebManager\Model;

use NAttreid\Orm\Structure\Table;
use Nette\Caching\Cache;

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
			->setDefault(NULL);
		$table->addColumn('keywords')
			->varChar()
			->setDefault(NULL);
		$table->addColumn('description')
			->varChar()
			->setDefault(NULL);
		$table->addColumn('content')
			->text()
			->setDefault(NULL);
		$table->addColumn('position')
			->int()
			->setKey();
		$table->setUnique('url', 'localeId');
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
		if ($rows === NULL) {
			$rows = $this->cache->save($key, function () {
				$result = [];
				foreach ($this->getRepository()->findAll() as $page) {
					/* @var $page Page */
					$result[$page->url] = TRUE;
				}
				return $result;
			}, [
				Cache::TAGS => [$this->tag]
			]);
		}
		if (isset($rows[$url])) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Vrati nejvetsi pozici
	 * @return int
	 */
	public function getMaxPosition()
	{
		return $this->connection->query('SELECT IFNULL(MAX([position]), 0) position FROM %table', $this->getTableName())->fetch()->position;
	}

}
