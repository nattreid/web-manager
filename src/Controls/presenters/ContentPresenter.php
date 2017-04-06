<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Presenters;

use NAttreid\Cms\LocaleService;
use NAttreid\Form\Form;
use NAttreid\Security\Model\Acl\Acl;
use NAttreid\WebManager\Model\Content\Content;
use NAttreid\WebManager\Model\Orm;
use Nette\Utils\ArrayHash;
use Nextras\Dbal\UniqueConstraintViolationException;
use Nextras\Orm\Model\Model;
use Ublaboo\DataGrid\DataGrid;

/**
 * Webovy obsah
 *
 * @author Attreid <attreid@gmail.com>
 */
class ContentPresenter extends BasePresenter
{

	/** @var Orm */
	private $orm;

	/** @var Content */
	private $content;

	/** @var LocaleService */
	private $localeService;

	/** @var bool */
	private $editConst;

	public function __construct(Model $orm, LocaleService $localeService)
	{
		parent::__construct();
		$this->orm = $orm;
		$this->localeService = $localeService;
	}

	protected function startup()
	{
		parent::startup();
		$this->editConst = $this->user->isAllowed('webManager.web.content.edit', Acl::PRIVILEGE_EDIT);
	}

	public function handleBack(string $backlink = null)
	{
		$this->redirect('default');
	}

	/**
	 * Smazani obsahu
	 * @param int $id
	 * @secured
	 */
	public function handleDelete(int $id)
	{
		if ($this->isAjax() && $this->editConst) {
			$content = $this->orm->content->getById($id);
			$this->orm->content->removeAndFlush($content);
			$this['list']->reload();
		} else {
			$this->terminate();
		}
	}

	/**
	 * Smaze obsahy
	 * @param array $ids
	 */
	public function deleteContent(array $ids)
	{
		if ($this->isAjax() && $this->editConst) {
			$pages = $this->orm->content->findById($ids);
			foreach ($pages as $page) {
				$this->orm->content->remove($page);
			}
			$this->orm->flush();
			$this['list']->reload();
		} else {
			$this->terminate();
		}
	}

	public function actionAdd()
	{
		if (!$this->editConst) {
			$this->error();
		}
	}

	/**
	 * Pridani obsahu
	 */
	public function renderAdd()
	{
		$this['editForm']->setDefaults([
			'locale' => $this->localeService->currentLocaleId
		]);
		$this->setView('edit');
	}

	/**
	 * Editace obsahu
	 * @param int $id
	 */
	public function actionEdit(int $id)
	{
		$this->content = $this->orm->content->getById($id);
		if (!$this->content) {
			$this->error();
		}
	}

	public function renderEdit()
	{
		$content = $this->content;
		$this->addBreadcrumbLink($content->name);
		$this['editForm']->setDefaults($content->toArray($content::TO_ARRAY_RELATIONSHIP_AS_ID));
	}

	/**
	 * Editace obsahu
	 * @return Form
	 */
	protected function createComponentEditForm(): Form
	{
		$form = $this->formFactory->create();

		$form->addText('name', 'default.name')
			->setRequired();

		if ($this->editConst) {
			$form->addText('const', 'webManager.web.content.const')
				->setRequired();

			$form->addSelectUntranslated('locale', 'webManager.web.pages.locale')
				->setItems($this->localeService->allowed);
		}

		$form->addText('title', 'webManager.web.content.contentTitle');

		$form->addTextArea('keywords', 'webManager.web.content.keywords');

		$form->addImageUpload('image', 'webManager.web.content.photo', 'webManager.web.content.photoDelete')
			->setNamespace('content')
			->setPreview('300x100');

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
	 * @param ArrayHash $values
	 */
	public function editFormSucceeded(Form $form, ArrayHash $values)
	{
		if ($this->content) {
			$content = $this->content;
		} else {
			$content = new Content;
			$this->orm->content->attach($content);
		}

		try {
			if ($this->editConst) {
				$content->locale = $values->locale;
				$content->setConst($values->const);
			}
			$content->name = $values->name;
			$content->title = $values->title;
			$content->keywords = $values->keywords;
			$content->image = $values->image;
			$content->description = $values->description;
			$content->content = $values->content;

			$this->orm->persistAndFlush($content);
			$this->restoreBacklink();
		} catch (UniqueConstraintViolationException $ex) {
			$form->addError('webManager.web.content.constantExists');
		}
	}

	/**
	 * Seznam obsahu
	 * @return DataGrid
	 */
	protected function createComponentList(): DataGrid
	{
		$grid = $this->dataGridFactory->create();
		$grid->setDataSource($this->orm->content->findAll());
		$grid->setDefaultSort(['name' => 'ASC']);

		if ($this->editConst) {
			$grid->addToolbarButton('add', 'webManager.web.content.add');
		}

		$grid->addColumnText('name', 'default.name')
			->setSortable()
			->setFilterText();

		$grid->addColumnText('const', 'webManager.web.content.const')
			->setSortable()
			->setFilterText();

		$grid->addColumnText('locale', 'webManager.web.content.locale')
			->setRenderer(function (Content $content) {
				return $content->locale->name;
			})
			->setFilterSelect($this->localeService->allowed);

		$grid->addAction('edit', null)
			->setIcon('pencil')
			->setTitle('default.edit');

		if ($this->editConst) {
			$grid->addAction('delete', null, 'delete!')
				->setIcon('trash')
				->setTitle('default.delete')
				->setClass('btn btn-xs btn-danger ajax')
				->setConfirm(function (Content $content) {
					return $this->translate('default.confirmDelete', 1, ['name' => $content->name]);
				});
		}

		$grid->setDefaultFilter(['locale' => $this->localeService->defaultLocaleId]);

		$grid->addGroupAction('default.delete')->onSelect[] = [$this, 'deletePages'];

		return $grid;
	}

}
