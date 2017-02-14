<?php

namespace NAttreid\WebManager\Services\Hooks;

use IPub\FlashMessages\FlashNotifier;
use NAttreid\Cms\Configurator\Configurator;
use NAttreid\Cms\Factories\FormFactory;
use NAttreid\Form\Form;
use Nette\Reflection\ClassType;
use Nette\SmartObject;

/**
 * Class HookFactory
 *
 * @property-read string $name
 *
 * @author Attreid <attreid@gmail.com>
 */
abstract class HookFactory
{
	use SmartObject;

	/** @var FormFactory */
	protected $formFactory;

	/** @var Configurator */
	protected $configurator;

	/** @var FlashNotifier */
	protected $flashNotifier;

	/** @var string */
	private $name;

	public function __construct(FormFactory $formFactory, Configurator $configurator, FlashNotifier $flashNotifier)
	{
		$this->formFactory = $formFactory;
		$this->configurator = $configurator;
		$this->flashNotifier = $flashNotifier;

		$this->name = $this->createName();
	}

	/**
	 * Vrati nazev
	 * @return string
	 */
	private function createName()
	{
		$reflection = new ClassType(get_called_class());
		return str_replace('Hook', '', lcfirst($reflection->getShortName()));
	}

	/**
	 * @return string
	 */
	protected function getName()
	{
		return $this->name;
	}

	/** @return Form */
	public abstract function create();
}