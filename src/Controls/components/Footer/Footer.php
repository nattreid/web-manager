<?php

namespace NAttreid\WebManager\Components;

use NAttreid\Cms\Configurator\Configurator;
use Nette\Application\UI\Control;

/**
 * Class Footer
 *
 * @author Attreid <attreid@gmail.com>
 */
class Footer extends Control
{
	/** @var Configurator */
	private $configurator;

	public function __construct(Configurator $configurator)
	{
		parent::__construct();
		$this->configurator = $configurator;
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . '/default.latte');

		$this->template->googleAnalyticsClientId = $this->configurator->googleAnalyticsClientId;

		$this->template->render();
	}
}

interface IFooterFactory
{
	/** @return Footer */
	public function create();
}
