<?php

namespace NAttreid\WebManager\Model;

use NAttreid\AppManager\AppManager;

/**
 * @property-read ContentRepository $content
 * @property-read PagesRepository $pages
 *
 * @author Attreid <attreid@gmail.com>
 */
class Orm extends \Nextras\Orm\Model\Model
{

	public function setAppManager(AppManager $app)
	{
		$app->onInvalidateCache[] = function () {
			$this->pages->cleanCache();
		};
	}

}
