<?php

namespace NAttreid\WebManager\Model\PagesGalleries;

use NAttreid\Orm\Repository;
use NAttreid\WebManager\Model\Pages\Page;

/**
 * Class PagesGalleriesRepository
 *
 * @author Attreid <attreid@gmail.com>
 */
class PagesGalleriesRepository extends Repository
{
	/** @var PagesGalleriesMapper */
	protected $mapper;

	public static function getEntityClassNames()
	{
		return [PageGallery::class];
	}

	/**
	 * Vrati nejvetsi pozici
	 * @param Page $page
	 * @return int
	 */
	public function getMaxPosition($page)
	{
		return $this->mapper->getMaxPosition($page);
	}
}