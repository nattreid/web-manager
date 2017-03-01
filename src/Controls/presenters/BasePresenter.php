<?php

declare(strict_types = 1);

namespace NAttreid\WebManager\Presenters;

use NAttreid\Cms\Control\ModulePresenter;
use NAttreid\WebManager\IConfigurator;

/**
 * Zakladni presenter pro WebManager presentery
 *
 * @author Attreid <attreid@gmail.com>
 */
abstract class BasePresenter extends ModulePresenter
{
	/** @var IConfigurator */
	protected $configurator;
}
