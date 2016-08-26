<?php

namespace NAttreid\WebManager\Presenters;

use NAttreid\Form\Form;

/**
 * Hlavni nastaveni stranek
 *
 * @author Attreid <attreid@gmail.com>
 */
class SettingsPresenter extends BasePresenter {

    /**
     * Zobrazeni nastaveni
     */
    public function renderDefault() {
        /** @var $form Form */
        $form = $this['settingsForm'];

        $this->addBreadcrumbLink('webManager.web.settings');
        $form->setDefaults($this->configurator->fetchConfigurations());
    }

    /**
     * Hlavni nastaveni stranek
     * @return Form
     */
    protected function createComponentSettingsForm() {
        $form = $this->formFactory->create();

        $form->addCheckbox('cookiePolicy', 'webManager.web.setting.cookiePolicy');
        $form->addText('cookiePolicyLink', 'webManager.web.setting.cookiePolicyLink');
        $form->addText('title', 'webManager.web.setting.title');
        $form->addTextArea('keywords', 'webManager.web.setting.keywords');
        $form->addTextArea('description', 'webManager.web.setting.description');

        $form->addSubmit('save', 'form.save');

        $form->onSuccess[] = [$this, 'settingsFormSucceeded'];

        return $form;
    }

    /**
     * Ulozeni hlavniho nastaveni
     * @param Form $form
     * @param array $values
     */
    public function settingsFormSucceeded(Form $form, $values) {
        $form->setValues($values);
        foreach ($values as $name => $value) {
            $this->configurator->$name = $value;
        }
        $this->flashNotifier->success('webManager.web.setting.saved');

        if ($this->isAjax()) {
            $this->redrawControl('settings');
        } else {
            $this->redirect('default');
        }
    }

}
