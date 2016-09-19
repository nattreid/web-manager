<?php

namespace NAttreid\WebManager\Presenters;
use NAttreid\WebManager\IConfigurator;

/**
 * Zakladni presenter pro WebManager presentery
 *
 * @author Attreid <attreid@gmail.com>
 */
abstract class BasePresenter extends \NAttreid\Crm\Control\ExtensionPresenter
{
	/** @var IConfigurator */
	protected $configurator;
}
