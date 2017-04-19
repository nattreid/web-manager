<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Presenters;

/**
 * Domovska stranka spravy
 *
 * @author Attreid <attreid@gmail.com>
 */
class HomepagePresenter extends BasePresenter
{

	public function actionDefault(): void
	{
		$this->viewMobileMenu();
	}

}
