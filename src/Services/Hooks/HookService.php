<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Services\Hooks;

use NAttreid\WebManager\HookNotExistsException;
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
	 * @param string $name
	 * @return HookFactory
	 * @throws HookNotExistsException
	 */
	public function getHook(string $name): HookFactory
	{
		if (!isset($this->hooks[$name])) {
			throw new HookNotExistsException;
		}
		return $this->hooks[$name];
	}

	/**
	 * @return HookFactory[]
	 */
	protected function getHooks(): array
	{
		return $this->hooks;
	}

	/**
	 * @return string
	 */
	protected function getFirstHookName(): string
	{
		reset($this->hooks);
		return key($this->hooks);
	}

	/**
	 * Prida formular do nastaveni
	 * @param HookFactory $hook
	 */
	public function addHook(HookFactory $hook): void
	{
		$this->hooks[$hook->name] = $hook;
	}
}