<?php

namespace NAttreid\WebManager\Presenters;
use NAttreid\Crm\Control\ExtensionPresenter;
use NAttreid\WebManager\IConfigurator;

/**
 * Zakladni presenter pro WebManager presentery
 *
 * @author Attreid <attreid@gmail.com>
 */
abstract class BasePresenter extends ExtensionPresenter
{
	/** @var IConfigurator */
	protected $configurator;
}
