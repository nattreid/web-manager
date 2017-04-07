<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Model;

/**
 * Mapper
 *
 * @author Attreid <attreid@gmail.com>
 */
abstract class Mapper extends \NAttreid\Orm\Mapper
{

	public function getTablePrefix(): string
	{
		return '_';
	}

}
