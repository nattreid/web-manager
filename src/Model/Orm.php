<?php

namespace NAttreid\WebManager\Model;

use NAttreid\WebManager\Model\Content\ContentRepository;
use NAttreid\WebManager\Model\Pages\PagesRepository;
use NAttreid\WebManager\Model\PagesGroup\PagesGroupsRepository;
use Nextras\Orm\Model\Model;

/**
 * @property-read ContentRepository $content
 * @property-read PagesRepository $pages
 * @property-read PagesGroupsRepository $pagesGroups
 *
 * @author Attreid <attreid@gmail.com>
 */
class Orm extends Model
{

}
