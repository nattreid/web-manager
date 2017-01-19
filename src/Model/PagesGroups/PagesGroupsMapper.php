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
		$table->addColumn('name')
			->varChar(50);

		$this->afterCreateTable[] = function () {
			$this->insert([
				[
					'id' => 1,
					'name' => 'menu'
				], [
					'id' => 2,
					'name' => 'footer'
				]
			]);
		};
	}
}
