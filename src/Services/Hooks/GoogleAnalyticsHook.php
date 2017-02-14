<?php

namespace NAttreid\WebManager\Services\Hooks;

use NAttreid\Form\Form;
use NAttreid\WebManager\IConfigurator;

/**
 * Class GoogleAnalyticsHook
 *
 * @author Attreid <attreid@gmail.com>
 */
class GoogleAnalyticsHook extends HookFactory
{
	/** @var IConfigurator */
	protected $configurator;

	/** @return Form */
	public function create()
	{
		$form = $this->formFactory->create();
		$form->setAjaxRequest();

		$form->addText('clientId', 'webManager.web.hooks.googleAnalytics.clientId')
			->setDefaultValue($this->configurator->googleAnalyticsClientId);

		$form->addSubmit('save', 'form.save');

		$form->onSuccess[] = [$this, 'googleAnalyticsFormSucceeded'];

		return $form;
	}

	public function googleAnalyticsFormSucceeded(Form $form, $values)
	{
		$this->configurator->googleAnalyticsClientId = $values->clientId;

		$this->flashNotifier->success('default.dataSaved');
	}
}