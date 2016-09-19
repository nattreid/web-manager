<?php

namespace NAttreid\WebManager\Presenters;

use NAttreid\Form\Form;

/**
 * Hlavni nastaveni stranek
 *
 * @author Attreid <attreid@gmail.com>
 */
class SettingsPresenter extends BasePresenter
{

	/**
	 * Zobrazeni nastaveni
	 */
	public function actionDefault()
	{
		/* @var $form Form */
		$form = $this['settingsForm'];
		$form->setDefaults($this->configurator->fetchConfigurations());
	}

	/**
	 * Hlavni nastaveni stranek
	 * @return Form
	 */
	protected function createComponentSettingsForm()
	{
		$form = $this->formFactory->create();

		$form->addImageUpload('logo', 'webManager.web.settings.logo', 'webManager.web.settings.deleteLogo')
			->setNamespace('front')
			->setPreview('300x100');
		$form->addCheckbox('cookiePolicy', 'webManager.web.settings.cookiePolicy');
		$form->addText('cookiePolicyLink', 'webManager.web.settings.cookiePolicyLink');
		$form->addText('title', 'webManager.web.settings.pagesTitle');
		$form->addTextArea('keywords', 'webManager.web.settings.keywords');
		$form->addTextArea('description', 'webManager.web.settings.description');

		$form->addSubmit('save', 'form.save');

		$form->onSuccess[] = [$this, 'settingsFormSucceeded'];

		return $form;
	}

	/**
	 * Ulozeni hlavniho nastaveni
	 * @param Form $form
	 * @param array $values
	 */
	public function settingsFormSucceeded(Form $form, $values)
	{
		$form->setValues($values);
		foreach ($values as $name => $value) {
			$this->configurator->$name = $value;
		}
		$this->flashNotifier->success('webManager.web.settings.saved');

		if ($this->isAjax()) {
			$this->redrawControl('settings');
		} else {
			$this->redirect('default');
		}
	}

}
