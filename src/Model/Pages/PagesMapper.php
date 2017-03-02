<?php

declare(strict_types = 1);

namespace NAttreid\WebManager\Model\Pages;

use NAttreid\Cms\Model\Locale\Locale;
use NAttreid\Cms\Model\Locale\LocalesMapper;
use NAttreid\Cms\Model\Orm;
use NAttreid\Orm\Structure\Table;
use NAttreid\WebManager\Model\Mapper;
use NAttreid\WebManager\Model\PagesViews\PagesViewsMapper;
use Nette\Caching\Cache;
use Nextras\Orm\Entity\IEntity;

/**
 * Pages Mapper
 *
 * @author Attreid <attreid@gmail.com>
 */
class PagesMapper extends Mapper
{
	/** @var string */
	private $tag = 'netta/pages';

	/** @var string[] */
	private $pageList;

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
		$table->addForeignKey('parentId', $table, null);
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

		$relationTable = $table->createRelationTable(PagesViewsMapper::class);
		$relationTable->addForeignKey('pageId', $table);
		$relationTable->addForeignKey('pageGroupId', PagesViewsMapper::class);
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
	 * Vrati stranku podle url
	 * @param string $url
	 * @param string|Locale $locale
	 * @return IEntity|null
	 */
	public function getByUrl(string $url, string $locale)
	{
		/* @var $orm Orm */
		$orm = $this->getRepository()->getModel();
		if ($locale instanceof Locale) {
			$eLocale = $locale;
		} else {
			$eLocale = $orm->locales->getByLocale($locale);
		}

		$urls = $this->getPageList();
		if (isset($urls[$url])) {
			$builder = $this->builder()
				->andWhere('[id] = %i', $urls[$url])
				->andWhere('[localeId] = %i', $eLocale->id);
			return $this->fetch($builder);
		}
		return null;
	}

	/**
	 * Je URL v databazi
	 * @param string $url
	 * @return bool
	 */
	public function exists(string $url = null): bool
	{
		$urls = $this->getPageList();
		if (isset($urls[$url])) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return string[]
	 */
	private function getPageList(): array
	{
		if ($this->pageList === null) {
			$key = 'pagesList';
			$this->pageList = $this->cache->load($key);
			if ($this->pageList === null) {
				$this->pageList = $this->cache->save($key, function () {
					/* @var $repo PagesRepository */
					$repo = $this->getRepository();
					$result = [];
					foreach ($repo->findAll() as $page) {
						$result[$page->completeUrl] = $page->id;
					}
					return $result;
				}, [
					Cache::TAGS => [$this->tag]
				]);
			}
		}
		return $this->pageList;
	}

}
