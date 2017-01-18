<?php

namespace NAttreid\WebManager\Model;

use NAttreid\Cms\Model\LocalesMapper;
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
		$table->addColumn('position')
			->int()
			->setKey();
		$table->addUnique('url', 'localeId');

		$relationTable = $table->createRelationTable(PagesGroupsMapper::class);
		$relationTable->addForeignKey('pageId', $table);
		$relationTable->addForeignKey('pageGroupId', PagesGroupsMapper::class);
		$relationTable->setPrimaryKey('pageId', 'pageGroupId');
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

}
