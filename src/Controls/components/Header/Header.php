<?php

namespace NAttreid\WebManager\Components;

use NAttreid\Cms\Configurator\Configurator;
use Nette\Application\UI\Control;

/**
 * Class Header
 *
 * @author Attreid <attreid@gmail.com>
 */
class Header extends Control
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

		$this->template->webmasterHash = $this->configurator->webmasterHash;

		$this->template->render();
	}
}

interface IHeaderFactory
{
	/** @return Header */
	public function create();
}
