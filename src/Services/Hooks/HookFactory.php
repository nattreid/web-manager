<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Services\Hooks;

use IPub\FlashMessages\FlashNotifier;
use NAttreid\Cms\Configurator\Configurator;
use NAttreid\Cms\Factories\DataGridFactory;
use NAttreid\Cms\Factories\FormFactory;
use Nette\ComponentModel\Component;
use Nette\Reflection\ClassType;
use Nette\SmartObject;

/**
 * Class HookFactory
 *
 * @property-read string $name
 * @property string $latte
 * @property string $component
 *
 * @author Attreid <attreid@gmail.com>
 */
abstract class HookFactory
{
	use SmartObject;

	/**
	 * Prekresli hook pri zmene dat
	 * @var callable[]
	 */
	public $onDataChange;

	/** @var FormFactory */
	protected $formFactory;

	/** @var DataGridFactory */
	protected $gridFactory;

	/** @var Configurator */
	protected $configurator;

	/** @var FlashNotifier */
	protected $flashNotifier;

	/** @var string */
	private $name;

	/** @var string */
	private $latte;

	/** @var string */
	private $component;

	public function __construct(FormFactory $formFactory, DataGridFactory $gridFactory, Configurator $configurator, FlashNotifier $flashNotifier)
	{
		$this->formFactory = $formFactory;
		$this->gridFactory = $gridFactory;
		$this->configurator = $configurator;
		$this->flashNotifier = $flashNotifier;

		$this->name = $this->createName();

		$this->init();
	}

	public function init()
	{

	}

	/**
	 * Vrati nazev
	 * @return string
	 */
	private function createName(): string
	{
		$reflection = new ClassType(get_called_class());
		return str_replace('Hook', '', lcfirst($reflection->getShortName()));
	}

	/**
	 * @return string
	 */
	protected function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return string|null
	 */
	protected function getLatte()
	{
		return $this->latte;
	}

	/**
	 * @param string $latte
	 */
	protected function setLatte(string $latte)
	{
		$this->latte = (string) $latte;
	}

	/**
	 * @return string|null
	 */
	protected function getComponent()
	{
		return $this->component;
	}

	/**
	 * @param string $component
	 */
	protected function setComponent(string $component)
	{
		$this->component = (string) $component;
	}

	/** @return Component */
	public abstract function create(): Component;
}