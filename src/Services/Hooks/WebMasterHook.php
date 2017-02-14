<?php

namespace NAttreid\WebManager\Services\Hooks;

use NAttreid\Form\Form;
use NAttreid\WebManager\IConfigurator;

/**
 * Class WebMasterHook
 *
 * @author Attreid <attreid@gmail.com>
 */
class WebMasterHook extends HookFactory
{
	/** @var IConfigurator */
	protected $configurator;

	/** @return Form */
	public function create()
	{
		$form = $this->formFactory->create();
		$form->setAjaxRequest();

		$form->addText('hash', 'webManager.web.hooks.webMaster.hash')
			->setDefaultValue($this->configurator->webmasterHash);

		$form->addSubmit('save', 'form.save');

		$form->onSuccess[] = [$this, 'webMasterFormSucceeded'];

		return $form;
	}

	public function webMasterFormSucceeded(Form $form, $values)
	{
		$this->configurator->webmasterHash = $values->hash;

		$this->flashNotifier->success('default.dataSaved');
	}
}