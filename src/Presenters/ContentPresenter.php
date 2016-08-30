<?php

namespace NAttreid\WebManager\Presenters;

use NAttreid\Form\Form,
    Ublaboo\DataGrid\DataGrid,
    Nextras\Orm\Model\Model,
    NAttreid\WebManager\Model\Orm,
    NAttreid\WebManager\Model\Content;

/**
 * Webovy obsah
 *
 * @author Attreid <attreid@gmail.com>
 */
class ContentPresenter extends BasePresenter {

    /** @var Orm */
    private $orm;

    /** @var Content */
    private $content;

    public function __construct(Model $orm) {
        $this->orm = $orm;
    }

    /**
     * Zobrazeni seznamu
     */
    public function renderDefault() {
        $this->storeBacklink();
    }

    /**
     * {@inheritdoc }
     */
    public function restoreBacklink() {
        parent::restoreBacklink();
        $this->redirect('default');
    }

    /**
     * Smazani obsahu
     * @secured
     */
    public function handleDelete($id) {
        $grid = $this['list']; /* @var $grid DataGrid */
        if ($this->isAjax()) {
            $content = $this->orm->content->getById($id);
            $this->orm->content->removeAndFlush($content);
            $grid->reload();
        } else {
            $this->terminate();
        }
    }

    /**
     * Smaze obsahy
     * @param array $ids
     */
    public function deleteContent(array $ids) {
        $grid = $this['list']; /* @var $grid DataGrid */
        if ($this->isAjax()) {
            $pages = $this->orm->content->findById($ids);
            foreach ($pages as $page) {
                $this->orm->content->remove($page);
            }
            $this->orm->flush();
            $grid->reload();
        } else {
            $this->terminate();
        }
    }

    /**
     * Pridani obsahu
     */
    public function actionAdd() {
        $this->content = new Content;
        $this->setView('edit');
    }

    /**
     * Editace obsahu
     * @param int $id
     */
    public function actionEdit($id) {
        $this->content = $this->orm->content->getById($id);
        if (!$this->content) {
            $this->error();
        }
        /* @var $form Form */
        $form = $this['editForm'];
        $form->setDefaults($this->content->toArray());
    }

    /**
     * Editace obsahu
     * @return Form
     */
    protected function createComponentEditForm() {
        $form = $this->formFactory->create();

        $form->addText('name', 'webManager.web.content.name')
                ->setRequired();

        $form->addText('const', 'webManager.web.content.const')
                ->setRequired();

        $form->addText('title', 'webManager.web.content.contentTitle');

        $form->addTextArea('keywords', 'webManager.web.content.keywords');

        $form->addImageUpload('image', 'webManager.web.content.photo', 'webManager.web.content.photoDelete')
                ->setNamespace('content')
                ->setPreview();

        $form->addTextArea('description', 'webManager.web.content.description');

        $form->addTextEditor('content', 'webManager.web.content.content');

        $form->addSubmit('save', 'form.save');

        $form->addLink('back', 'form.back', $this->getBacklink());

        $form->onSuccess[] = [$this, 'editFormSucceeded'];

        return $form;
    }

    /**
     * Ulozeni obsahu
     * @param Form $form
     * @param array $values
     */
    public function editFormSucceeded(Form $form, $values) {
        $content = $this->content;
        if (!$content->isAttached()) {
            $this->orm->content->attach($content);
        }
        try {
            $content->name = $values->name;
            $content->setConst($values->const);
            $content->title = $values->title;
            $content->keywords = $values->keywords;
            $content->image = $values->image;
            $content->description = $values->description;
            $content->content = $values->content;

            $this->orm->persistAndFlush($content);
            $this->restoreBacklink();
        } catch (\Nextras\Dbal\UniqueConstraintViolationException $ex) {
            $form->addError('webManager.web.content.constantExists');
        }
    }

    /**
     * Seznam obsahu
     * @return DataGrid
     */
    protected function createComponentList() {
        $grid = $this->dataGridFactory->create();

        $grid->setDataSource($this->orm->content->findAll());

        $grid->setDefaultSort(['name' => 'ASC']);

        $grid->addToolbarButton('add', 'webManager.web.content.add');

        $grid->addColumnText('name', 'webManager.web.content.name')
                ->setSortable()
                ->setFilterText();

        $grid->addColumnText('const', 'webManager.web.content.const')
                ->setSortable()
                ->setFilterText();

        $grid->addAction('edit', NULL)
                ->setIcon('pencil')
                ->setTitle('webManager.web.content.edit');

        $grid->addAction('delete', NULL, 'delete!')
                ->setIcon('trash')
                ->setTitle('webManager.web.content.delete')
                ->setClass('btn btn-xs btn-danger ajax')
                ->setConfirm(function(Content $content) {
                    return $this->translate('webManager.web.content.confirmDelete', 1, ['name' => $content->name]);
                });

        $grid->addGroupAction('webManager.web.content.delete')->onSelect[] = [$this, 'deletePages'];

        return $grid;
    }

}
