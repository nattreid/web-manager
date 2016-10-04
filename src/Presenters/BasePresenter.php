<?php

namespace NAttreid\WebManager\Presenters;

use NAttreid\Crm\Control\ModulePresenter;
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
