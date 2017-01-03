<?php

namespace NAttreid\WebManager\Presenters;

use NAttreid\Form\Form;

/**
 * Class ServicesPresenter
 *
 * @author Attreid <attreid@gmail.com>
 */
class HooksPresenter extends BasePresenter
{
	protected function createComponentGoogleAnalyticsForm()
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
		$this->configurator->googleAnalyticsClientId = $values->googleAnalyticsClientId;

		$this->flashNotifier->success('default.dataSaved');
		if (!$this->isAjax()) {
			$this->redirect('this');
		}
	}

	protected function createComponentWebMasterForm()
	{
		$form = $this->formFactory->create();
		$form->setAjaxRequest();

		$form->addText('clientId', 'webManager.web.hooks.webMaster.hash')
			->setDefaultValue($this->configurator->webmasterHash);

		$form->addSubmit('save', 'form.save');

		$form->onSuccess[] = [$this, 'webMasterFormSucceeded'];

		return $form;
	}

	public function webMasterFormSucceeded(Form $form, $values)
	{
		$this->configurator->webmasterHash = $values->webmasterHash;

		$this->flashNotifier->success('default.dataSaved');
		if (!$this->isAjax()) {
			$this->redirect('this');
		}
	}
}