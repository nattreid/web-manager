<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Model\Content;

use NAttreid\Cms\Model\Locale\LocalesMapper;
use NAttreid\Orm\Structure\Table;
use NAttreid\WebManager\Model\Mapper;

/**
 * Contents Mapper
 *
 * @author Attreid <attreid@gmail.com>
 */
class ContentMapper extends Mapper
{

	protected function createTable(Table $table): void
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
			->text();
		$table->addUnique('const', 'localeId');
	}

}
