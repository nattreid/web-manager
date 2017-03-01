<?php

declare(strict_types = 1);

namespace NAttreid\WebManager\Model\PagesViews;

use NAttreid\Orm\Structure\Table;
use NAttreid\WebManager\Model\Mapper;

/**
 * PagesViews Mapper
 *
 * @author Attreid <attreid@gmail.com>
 */
class PagesViewsMapper extends Mapper
{

	const
		MENU = 1,
		FOOTER = 2;

	protected function createTable(Table $table)
	{
		$table->addPrimaryKey('id')
			->int()
			->setAutoIncrement();
		$table->addColumn('name')
			->varChar(50);

		$this->afterCreateTable[] = function () {
			$this->insert([
				[
					'id' => self::MENU,
					'name' => 'menu'
				], [
					'id' => self::FOOTER,
					'name' => 'footer'
				]
			]);
		};
	}
}
