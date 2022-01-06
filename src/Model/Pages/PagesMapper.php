<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Model\Pages;

use NAttreid\Cms\Model\Locale\Locale;
use NAttreid\Cms\Model\Locale\LocalesMapper;
use NAttreid\Cms\Model\Orm;
use NAttreid\Orm\Structure\Table;
use NAttreid\WebManager\Model\Mapper;
use NAttreid\WebManager\Model\PagesViews\PagesViewsMapper;
use Nette\Caching\Cache;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\IEntity;
use Throwable;

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

	protected function createTable(Table $table): void
	{
		$table->addPrimaryKey('id')
			->int()
			->setAutoIncrement();
		$table->addColumn('name')
			->varChar(100);
		$table->addColumn('url')
			->varChar(100)
			->setDefault(null);
		$table->addForeignKey('localeId', LocalesMapper::class);
		$table->addForeignKey('parentId', $table, null);
		$table->addColumn('title')
			->varChar(150)
			->setDefault(null);
		$table->addColumn('showTitle')
			->bool()
			->setDefault(1);
		$table->addColumn('image')
			->varChar(150)
			->setDefault(null);
		$table->addColumn('background')
			->varChar(10)
			->setDefault(null);
		$table->addColumn('keywords')
			->varChar()
			->setDefault(null);
		$table->addColumn('description')
			->varChar()
			->setDefault(null);
		$table->addColumn('content')
			->longtext()
			->setDefault(null);
		$table->addColumn('position')
			->int()
			->setKey();
		$table->addColumn('visible')
			->bool()
			->setDefault(1)
			->setKey();
		$table->addColumn('isLink')
			->bool()
			->setDefault(0);
		$table->addUnique('url', 'localeId', 'parentId');

		$relationTable = $table->createRelationTable(PagesViewsMapper::class);
		$relationTable->addForeignKey('pageId', $table);
		$relationTable->addForeignKey('pageViewId', PagesViewsMapper::class);
		$relationTable->setPrimaryKey('pageId', 'pageViewId');
	}

	/**
	 * Smaze cache
	 */
	public function cleanCache(): void
	{
		$this->cache->clean([
			Cache::TAGS => [$this->tag]
		]);
	}

	/**
	 * Vrati lokalizovane stranky bez HP
	 * @param string $locale
	 * @return ICollection
	 */
	public function findByLocale(string $locale): ICollection
	{
		$builder = $this->builder()
			->innerJoin('_pages', '[_locales]', 'l', '_pages.localeId = l.id')
			->andWhere('[url] IS NOT NULL')
			->andWhere('[visible] = %i', 1)
			->andWhere('[l.name] = %s', $locale);
		return $this->toCollection($builder);
	}

	/**
	 * Vrati stranku podle url
	 * @param string|null $url
	 * @param string|Locale $locale
	 * @return IEntity|Page|null
	 * @throws Throwable
	 */
	public function getByUrl(?string $url, $locale): ?Page
	{
		/* @var $orm Orm */
		$orm = $this->getRepository()->getModel();
		if ($locale instanceof Locale) {
			$eLocale = $locale;
		} else {
			$eLocale = $orm->locales->getByLocale($locale);
		}

		$urls = $this->getPageList();
		if (isset($urls[$eLocale->name][$url])) {
			$builder = $this->builder()
				->andWhere('[id] = %i', $urls[$eLocale->name][$url])
				->andWhere('[localeId] = %i', $eLocale->id);
			return $this->toEntity($builder);
		}
		return null;
	}

	/**
	 * Je URL v databazi
	 * @param string|null $url
	 * @param string|null $locale
	 * @return bool
	 * @throws Throwable
	 */
	public function exists(?string $url, string $locale): bool
	{
		$urls = $this->getPageList();
		if (isset($urls[$locale][$url])) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return string[]
	 * @throws Throwable
	 */
	private function getPageList(): array
	{
		if ($this->pageList === null) {
			$key = 'nattreidWebManagerPagesList';
			$this->pageList = $this->cache->load($key);
			if ($this->pageList === null) {
				$this->pageList = $this->cache->save($key, function () {
					/* @var $repo PagesRepository */
					$repo = $this->getRepository();
					$result = [];
					$rows = $repo->findAll()->findBy([
						'isLink' => 0,
						'visible' => 1
					]);
					foreach ($rows as $page) {
						$locale = $page->locale->name;
						if (!isset($result[$locale])) {
							$result[$locale] = [];
						}
						$result[$locale][$page->completeUrl] = $page->id;
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
