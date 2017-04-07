<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Presenters;

use InvalidArgumentException;
use NAttreid\WebManager\Services\Hooks\HookService;
use Nette\Forms\Form;
use Ublaboo\DataGrid\DataGrid;

/**
 * Class ServicesPresenter
 *
 * @author Attreid <attreid@gmail.com>
 */
class HooksPresenter extends BasePresenter
{

	/** @var HookService */
	private $hookService;

	public function __construct(HookService $hookService)
	{
		parent::__construct();
		$this->hookService = $hookService;
	}

	public function renderDefault(string $tab = null)
	{
		$this->template->tab = $tab ?: $this->hookService->firstHookName;
		$this->template->hooks = $this->hookService->hooks;
	}

	protected function createComponent($name)
	{
		try {
			$hook = $this->hookService->getHook($name);
			$component = $hook->create();

			if ($component instanceof Form) {
				$component->onSuccess[] = function () use ($name) {
					if (!$this->isAjax()) {
						$this->redirect('this', $name);
					}
				};
			} elseif ($component instanceof DataGrid) {
				$component->getInlineAdd()->onSubmit[] = function () use ($component, $name) {
					if ($this->isAjax()) {
						$this[$name]->reload();
					}
				};
				$hook->onDeleteEvent[] = function ($key) use ($name) {
					$this[$name]->reload();
				};
			}

			return $component;
		} catch (InvalidArgumentException $ex) {
			return parent::createComponent($name);
		}
	}
}