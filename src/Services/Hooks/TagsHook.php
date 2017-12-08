<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Services\Hooks;

use NAttreid\Form\Form;
use NAttreid\WebManager\IConfigurator;
use Nette\ComponentModel\Component;
use Nette\Utils\ArrayHash;

/**
 * Class TagsHook
 *
 * @author Attreid <attreid@gmail.com>
 */
class TagsHook extends HookFactory
{
	/** @var IConfigurator */
	protected $configurator;

	/** @return Component */
	public function create(): Component
	{
		$form = $this->formFactory->create();
		$form->setAjaxRequest();

		$form->addTextArea('tags', 'webManager.web.hooks.tags.content')
			->setDefaultValue($this->configurator->tags ?: null);

		$form->addSubmit('save', 'form.save');

		$form->onSuccess[] = [$this, 'tagsFormSucceeded'];

		return $form;
	}

	public function tagsFormSucceeded(Form $form, ArrayHash $values)
	{
		$this->configurator->tags = $values->tags;
		$this->flashNotifier->success('default.dataSaved');
	}
}