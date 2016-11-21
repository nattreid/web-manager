<?php

namespace NAttreid\WebManager\Model;

use NAttreid\Orm\Repository;

/**
 * PagesGroups Repository
 *
 * @author Attreid <attreid@gmail.com>
 */
class PagesGroupsRepository extends Repository
{

	/** @var PagesMapper */
	protected $mapper;


	public static function getEntityClassNames()
	{
		return [PageGroup::class];
	}
}
