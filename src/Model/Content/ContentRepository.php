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

	public static function getEntityClassNames(): array
	{
		return [Content::class];
	}

	/**
	 * Vrati obsah podle konstanty
	 * @param string $const
	 * @param string $locale
	 * @return Content|null
	 */
	public function getByConst(string $const, string $locale): ?Content
	{
		return $this->getBy([
			'const' => $const,
			'locale->name' => $locale
		]);
	}

}
