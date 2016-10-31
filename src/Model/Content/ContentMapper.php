<?php

namespace NAttreid\WebManager\Model;

use NAttreid\Crm\Model\LocalesMapper;
use NAttreid\Orm\Structure\Table;

/**
 * Contents Mapper
 *
 * @author Attreid <attreid@gmail.com>
 */
class ContentMapper extends Mapper
{

	protected function createTable(Table $table)
	{
		$table->addPrimaryKey('id')
			->int()
			->setAutoIncrement();
		$table->addColumn('name')
			->varChar(50);
		$table->addColumn('const')
			->varChar(30);
		$table->addForeignKey('localeId', LocalesMapper::class);
		$table->addColumn('title')
			->varChar(150)
			->setDefault(null);
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
			->text();
		$table->addUnique('const', 'localeId');
	}

}
