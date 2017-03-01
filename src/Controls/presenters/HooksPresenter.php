<?php

declare(strict_types = 1);

namespace NAttreid\WebManager\Presenters;

use InvalidArgumentException;
use NAttreid\WebManager\Services\Hooks\HookService;

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

	public function renderDefault(string $tab)
	{
		$this->template->tab = $tab ?: $this->hookService->firstHookName;
		$this->template->hooks = $this->hookService->hooks;
	}

	protected function createComponent($name)
	{
		try {
			$form = $this->hookService->getHook($name)->create();

			$form->onSuccess[] = function () use ($name) {
				if (!$this->isAjax()) {
					$this->redirect('this', $name);
				}
			};

			return $form;
		} catch (InvalidArgumentException $ex) {
			return parent::createComponent($name);
		}
	}
}