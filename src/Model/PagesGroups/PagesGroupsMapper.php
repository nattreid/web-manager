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
		$table->addPrimaryKey('id')
			->int()
			->setAutoIncrement();
		$table->addForeignKey('pageId', PagesMapper::class);
		$table->addColumn('group')
			->int()
			->setKey();
		$table->addUnique('pageId', 'group');
	}
}
