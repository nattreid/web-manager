<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Model\PagesGalleries;

use NAttreid\Orm\Repository;
use Nextras\Dbal\QueryException;

/**
 * Class PagesGalleriesRepository
 *
 * @method int getMaxPosition(int $pageId) Vrati nejvetsi pozici
 *
 * @author Attreid <attreid@gmail.com>
 */
class PagesGalleriesRepository extends Repository
{

	public static function getEntityClassNames(): array
	{
		return [PageGallery::class];
	}
}