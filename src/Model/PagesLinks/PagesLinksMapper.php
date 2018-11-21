<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Model\PagesLinks;

use NAttreid\Orm\Structure\Table;
use NAttreid\WebManager\Model\Mapper;
use NAttreid\WebManager\Model\PagesLinksGroups\PagesLinksGroupsMapper;

/**
 * Class PagesLinksMapper
 *
 * @author Attreid <attreid@gmail.com>
 */
class PagesLinksMapper extends Mapper
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
		$table->addForeignKey('groupId', PagesLinksGroupsMapper::class);
		$table->addColumn('name')
			->text();
		$table->addColumn('url')
			->varChar();
		$table->addColumn('content')
			->text()
			->setDefault(null);
		$table->addColumn('image')
			->varChar()
			->setDefault(null);
		$table->addColumn('position')
			->int(3);
		$table->addColumn('visible')
			->bool()
			->setDefault(1);
		$table->addColumn('openNewWindow')
			->bool()
			->setDefault(0);
	}
}