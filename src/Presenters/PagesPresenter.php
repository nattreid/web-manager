<?php

namespace NAttreid\WebManager\Presenters;

use NAttreid\Form\Form,
    Nextras\Orm\Model\Model,
    NAttreid\WebManager\Model\Orm,
    NAttreid\WebManager\Model\Page,
    Ublaboo\DataGrid\DataGrid;

/**
 * Stranky
 *
 * @author Attreid <attreid@gmail.com>
 */
class PagesPresenter extends BasePresenter {

    /** @var Orm */
    private $orm;

    /** @var Page */
    private $page;

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
     * Smazani stranky
     * @secured
     */
    public function handleDelete($id) {
        /* @var $grid Datagrid */
        $grid = $this['list'];

        if ($this->isAjax()) {
            $page = $this->orm->pages->getById($id);
            $this->orm->pages->removeAndFlush($page);
            $grid->reload();
        } else {
            $this->terminate();
        }
    }

    /**
     * Smaze stranky
     * @param array $ids
     */
    public function deletePages(array $ids) {
        /* @var $grid Datagrid */
        $grid = $this['list'];

        if ($this->isAjax()) {
            $pages = $this->orm->pages->findById($ids);
            foreach ($pages as $page) {
                $this->orm->pages->remove($page);
            }
            $this->orm->flush();
            $grid->reload();
        } else {
            $this->terminate();
        }
    }

    /**
     * Serazeni
     * @param type $item_id
     * @param type $prev_id
     * @param type $next_id
     */
    public function handleSort($item_id, $prev_id, $next_id) {
        $this->orm->pages->changeSort($item_id, $prev_id, $next_id);
    }

    /**
     * Pridani stranky
     */
    public function actionAdd() {
        $this->page = new Page;
        $this->setView('edit');
    }

    /**
     * Editace stranky
     * @param int $id
     */
    public function actionEdit($id) {
        $this->page = $this->orm->pages->getById($id);
        if (!$this->page) {
            $this->error();
        }
        /* @var $form Form */
        $form = $this['editForm'];
        $form->setDefaults($this->page->toArray());
    }

    /**
     * Editace stranky
     * @return Form
     */
    protected function createComponentEditForm() {
        $form = $this->formFactory->create();

        $form->addText('name', 'webManager.web.page.name')
                ->setRequired();

        $form->addText('url', 'webManager.web.page.url');

        $form->addText('title', 'webManager.web.page.title')
                ->setRequired();

        $form->addTextArea('keywords', 'webManager.web.page.keywords');

        $form->addImageUpload('image', 'webManager.web.page.photo', 'webManager.web.page.photoDelete')
                ->setNamespace('pages')
                ->setPreview();

        $form->addTextArea('description', 'webManager.web.page.description');

        $form->addTextEditor('content', 'webManager.web.page.content');

        $form->addSubmit('save', 'form.save');

        $form->addLink('back', 'form.back', $this->getBacklink());

        $form->onSuccess[] = [$this, 'editFormSucceeded'];

        return $form;
    }

    /**
     * Ulozeni stranky
     * @param Form $form
     * @param array $values
     */
    public function editFormSucceeded(Form $form, $values) {
        $page = $this->page;
        if (!$page->isAttached()) {
            $this->orm->pages->attach($page);
        }
        try {
            $page->name = $values->name;
            $page->setUrl($values->url);
            $page->title = $values->title;
            $page->keywords = $values->keywords;
            $page->image = $values->image;
            $page->description = $values->description;
            $page->content = $values->content;

            $this->orm->persistAndFlush($page);
            $this->restoreBacklink();
        } catch (\Nextras\Dbal\UniqueConstraintViolationException $ex) {
            $form->addError('webManager.web.page.urlExists');
        }
    }

    /**
     * Seznam stranek
     * @return DataGrid
     */
    protected function createComponentList() {
        $grid = $this->dataGridFactory->create();

        $grid->setDataSource($this->orm->pages->findAll());

        $grid->setSortable();

        $grid->addToolbarButton('add', 'webManager.web.page.add');

        $grid->addColumnText('name', 'webManager.web.page.name')
                ->setFilterText();

        $grid->addColumnText('url', 'webManager.web.page.url')
                ->setFilterText();

        $grid->addAction('edit', NULL)
                ->setIcon('pencil')
                ->setTitle('webManager.web.content.edit');

        $grid->addAction('delete', NULL, 'delete!')
                ->setIcon('trash')
                ->setTitle('webManager.web.content.delete')
                ->setClass('btn btn-xs btn-danger ajax')
                ->setConfirm(function(Page $page) {
                    return $this->translate('webManager.web.content.confirmDelete', 1, ['name' => $page->name]);
                });

        $grid->addGroupAction('webManager.web.content.delete')->onSelect[] = [$this, 'deletePages'];

        return $grid;
    }

}
