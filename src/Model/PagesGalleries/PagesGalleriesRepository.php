<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Model\PagesGalleries;

use NAttreid\Orm\Repository;
use Nextras\Dbal\QueryException;

/**
 * Class PagesGalleriesRepository
 *
 * @author Attreid <attreid@gmail.com>
 */
class PagesGalleriesRepository extends Repository
{
	/** @var PagesGalleriesMapper */
	protected $mapper;

	public static function getEntityClassNames(): array
	{
		return [PageGallery::class];
	}

	/**
	 * Vrati nejvetsi pozici
	 * @param int $pageId
	 * @return int
	 * @throws QueryException
	 */
	public function getMaxPosition(int $pageId): int
	{
		return $this->mapper->getMaxPosition($pageId);
	}
}