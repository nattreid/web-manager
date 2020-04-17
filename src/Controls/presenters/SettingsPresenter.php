<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Presenters;

use NAttreid\Form\Form;
use Nette\Application\AbortException;
use Nette\Http\FileUpload;
use Nette\Utils\ArrayHash;

/**
 * Hlavni nastaveni stranek
 *
 * @author Attreid <attreid@gmail.com>
 */
class SettingsPresenter extends BasePresenter
{
	/** @var string */
	private $favicon;

	public function setDir(string $dir): void
	{
		$this->favicon = $dir . '/favicon.ico';
	}
	
	/**
	 * Zobrazeni nastaveni
	 */
	public function renderDefault(): void
	{
		$this['settingsForm']->setDefaults($this->configurator->fetchConfigurations());
	}

	public function handleDeleteFavicon(): void
	{
		@unlink($this->favicon);
		$this->redirect('default');
	}

	/**
	 * Hlavni nastaveni stranek
	 * @return Form
	 */
	protected function createComponentSettingsForm(): Form
	{
		$form = $this->formFactory->create();

		$form->addImageUpload('logo', 'webManager.web.settings.logo', 'webManager.web.settings.deleteLogo')
			->setNamespace('front')
			->setPreview('300x100');
		$form->addText('title', 'webManager.web.settings.pagesTitle');
		$form->addTextArea('keywords', 'webManager.web.settings.keywords');
		$form->addTextArea('description', 'webManager.web.settings.description');
		$form->addUpload('favicon', 'webManager.web.settings.favicon');
		if (file_exists($this->favicon)) {
			$form->addLink('faviconDelete', 'webManager.web.settings.faviconDelete', $this->link('deleteFavicon!'))
				->setAttribute('class', 'btn-danger');
		}
		
		$form->addSubmit('save', 'form.save');

		$form->onSuccess[] = [$this, 'settingsFormSucceeded'];

		return $form;
	}

	/**
	 * Ulozeni hlavniho nastaveni
	 * @param Form $form
	 * @param array $values
	 * @throws AbortException
	 */
	public function settingsFormSucceeded(Form $form, ArrayHash $values): void
	{
		$this->configurator->logo = $values->logo;
		$this->configurator->title = $values->title;
		$this->configurator->keywords = $values->keywords;
		$this->configurator->description = $values->description;

		/* @var $favicon FileUpload */
		$favicon = $values->favicon;
		if ($values->favicon !== null && $favicon->isOk()) {
			$favicon->move($this->favicon);
		}
		
		$this->flashNotifier->success('webManager.web.settings.saved');

		if ($this->isAjax()) {
			$this->redrawControl('settings');
		} else {
			$this->redirect('default');
		}
	}

}
