<?php

namespace NAttreid\WebManager\Model;
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
			->varChar(30)
			->setUnique();
		$table->addColumn('title')
			->varChar(150)
			->setDefault(NULL);
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
			->text();
	}

}
