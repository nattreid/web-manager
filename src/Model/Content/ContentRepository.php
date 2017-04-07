<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Model\Content;

use NAttreid\Orm\Repository;

/**
 * Contents Repository
 *
 * @author Attreid <attreid@gmail.com>
 */
class ContentRepository extends Repository
{

	public static function getEntityClassNames()
	{
		return [Content::class];
	}

	/**
	 * Vrati obsah podle konstanty
	 * @param string $const
	 * @param string $locale
	 * @return Content|null
	 */
	public function getByConst(string $const, string $locale)
	{
		return $this->getBy([
			'const' => $const,
			'this->locale->name' => $locale
		]);
	}

}
