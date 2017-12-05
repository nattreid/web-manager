<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Model\PagesGalleries;

use NAttreid\Orm\Structure\Table;
use NAttreid\WebManager\Model\Mapper;
use NAttreid\WebManager\Model\Pages\PagesMapper;
use Nextras\Dbal\QueryException;

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
	protected function createTable(Table $table): void
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
	 * @param int $pageId
	 * @return int
	 * @throws QueryException
	 */
	public function getMaxPosition(int $pageId): int
	{
		return $this->connection->query('SELECT IFNULL(MAX([position]), 0) position FROM %table WHERE [pageId] = %i', $this->getTableName(), $pageId)->fetch()->position;
	}
}