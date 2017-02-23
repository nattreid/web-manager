<?php

namespace NAttreid\WebManager\Model\PagesGalleries;

use NAttreid\Orm\Structure\Table;
use NAttreid\WebManager\Model\Mapper;
use NAttreid\WebManager\Model\Pages\Page;
use NAttreid\WebManager\Model\Pages\PagesMapper;

/**
 * Class PagesGalleriesMapper
 *
 * @author Attreid <attreid@gmail.com>
 */
class PagesGalleriesMapper extends Mapper
{

	/**
	 * Nastavi strukturu tabulky
	 * @param Table $table
	 */
	protected function createTable(Table $table)
	{
		$table->addPrimaryKey('id')
			->int()
			->setAutoIncrement();
		$table->addForeignKey('pageId', PagesMapper::class);
		$table->addColumn('name')
			->varChar();
		$table->addColumn('position')
			->int(3);
	}

	/**
	 * Vrati nejvetsi pozici
	 * @param Page $page
	 * @return int
	 */
	public function getMaxPosition($page)
	{
		return $this->connection->query('SELECT IFnull(MAX([position]), 0) position FROM %table WHERE [pageId] = %i', $this->getTableName(), $page->id)->fetch()->position;
	}
}