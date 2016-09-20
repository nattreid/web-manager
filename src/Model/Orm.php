<?php

namespace NAttreid\WebManager\Model;

use NAttreid\AppManager\AppManager;
use Nextras\Orm\Model\Model;

/**
 * @property-read ContentRepository $content
 * @property-read PagesRepository $pages
 *
 * @author Attreid <attreid@gmail.com>
 */
class Orm extends Model
{

	public function setAppManager(AppManager $app)
	{
		$app->onInvalidateCache[] = function () {
			$this->pages->cleanCache();
		};
	}

}
