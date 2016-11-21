<?php

namespace NAttreid\WebManager\Model;

use NAttreid\Orm\Structure\Table;

/**
 * PagesGroups Mapper
 *
 * @author Attreid <attreid@gmail.com>
 */
class PagesGroupsMapper extends Mapper
{

	protected function createTable(Table $table)
	{
		$table->setDefaultDataFile(__DIR__ . '/groups.sql');

		$table->addPrimaryKey('id')
			->int()
			->setAutoIncrement();
		$table->addColumn('name')
			->varChar(50);
	}
}
