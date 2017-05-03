<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Model;

use NAttreid\WebManager\Model\PagesLinks\PagesLinksRepository;
use NAttreid\WebManager\Model\PagesLinksGroups\PagesLinksGroupsRepository;
use NAttreid\WebManager\Model\Content\ContentRepository;
use NAttreid\WebManager\Model\Pages\PagesRepository;
use NAttreid\WebManager\Model\PagesGalleries\PagesGalleriesRepository;
use NAttreid\WebManager\Model\PagesViews\PagesViewsRepository;
use Nextras\Orm\Model\Model;

/**
 * @property-read ContentRepository $content
 * @property-read PagesRepository $pages
 * @property-read PagesViewsRepository $pagesViews
 * @property-read PagesGalleriesRepository $pagesGalleries
 * @property-read PagesLinksGroupsRepository $pagesLinksGroups
 * @property-read PagesLinksRepository $pagesLinks
 *
 * @author Attreid <attreid@gmail.com>
 */
class Orm extends Model
{

}
