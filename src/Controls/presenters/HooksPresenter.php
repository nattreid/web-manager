<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Presenters;

use NAttreid\WebManager\HookNotExistsException;
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

	public function renderDefault(string $tab = null): void
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
				$component->setAction($this->link('this', $name));
				$hook->onDataChange[] = function () use ($name) {
					if (!$this->isAjax()) {
						$this->redirect('this', $name);
					}
				};
			} elseif ($component instanceof DataGrid) {
				$hook->onDataChange[] = function () use ($name) {
					if ($this->isAjax()) {
						$this[$name]->reload();
					}
				};
			}

			return $component;
		} catch (HookNotExistsException $ex) {
			return parent::createComponent($name);
		}
	}
}