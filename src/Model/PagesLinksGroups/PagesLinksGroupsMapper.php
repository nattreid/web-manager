<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Model\PagesLinksGroups;

use NAttreid\Orm\Structure\Table;
use NAttreid\WebManager\Model\Mapper;
use NAttreid\WebManager\Model\Pages\PagesMapper;

/**
 * Class PagesLinkGroupsMapper
 *
 * @author Attreid <attreid@gmail.com>
 */
class PagesLinksGroupsMapper extends Mapper
{
	/**
	 * Nastavi strukturu tabulky
	 * @param Table $table
	 */
	protected function createTable(Table $table): void
	{
		$table->addPrimaryKey('id')
			->int()
			->setAutoIncrement();
		$table->addForeignKey('pageId', PagesMapper::class);
		$table->addColumn('name')
			->text();
		$table->addColumn('quantity')
			->tinyint(2)
			->setDefault(null);
		$table->addColumn('position')
			->int(3);
		$table->addColumn('visible')
			->bool()
			->setDefault(1);
	}
}