<?php

namespace NAttreid\WebManager\Services\Hooks;

use InvalidArgumentException;
use Nette\SmartObject;

/**
 * Class HookService
 *
 * @property-read HookFactory[] $hooks
 * @property-read string $firstHookName
 *
 * @author Attreid <attreid@gmail.com>
 */
class HookService
{
	use SmartObject;

	/** @var HookFactory[] */
	private $hooks = [];

	/**
	 * @return HookFactory
	 * @throw InvalidArgumentException
	 */
	public function getHook($name)
	{
		if (!isset($this->hooks[$name])) {
			throw new InvalidArgumentException();
		}
		return $this->hooks[$name];
	}

	/**
	 * @return HookFactory[]
	 */
	protected function getHooks()
	{
		return $this->hooks;
	}

	/**
	 * @return string
	 */
	protected function getFirstHookName()
	{
		reset($this->hooks);
		return key($this->hooks);
	}

	/**
	 * Prida formular do nastaveni
	 * @param HookFactory $hook
	 */
	public function addHook(HookFactory $hook)
	{
		$this->hooks[$hook->name] = $hook;
	}
}