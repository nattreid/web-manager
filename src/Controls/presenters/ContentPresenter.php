<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Presenters;

use NAttreid\Cms\LocaleService;
use NAttreid\Form\Control\Spectrum\Color;
use NAttreid\Form\Form;
use NAttreid\Security\Model\Acl\Acl;
use NAttreid\WebManager\Model\Content\Content;
use NAttreid\WebManager\Model\Orm;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Utils\ArrayHash;
use Nextras\Dbal\UniqueConstraintViolationException;
use Nextras\Orm\Entity\ToArrayConverter;
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

	/**
	 * @throws AbortException
	 */
	protected function startup(): void
	{
		parent::startup();
		$this->editConst = $this->user->isAllowed('webManager.web.content.edit', Acl::PRIVILEGE_EDIT);
	}

	/**
	 * @param string|null $backlink
	 * @throws AbortException
	 */
	public function handleBack(string $backlink = null): void
	{
		$this->redirect('default');
	}

	/**
	 * Smazani obsahu
	 * @param int $id
	 * @secured
	 * @throws AbortException
	 */
	public function handleDelete(int $id): void
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
	 * @throws AbortException
	 */
	public function deleteContent(array $ids): void
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

	/**
	 * @throws BadRequestException
	 */
	public function actionAdd(): void
	{
		if (!$this->editConst) {
			$this->error();
		}
	}

	/**
	 * Pridani obsahu
	 */
	public function renderAdd(): void
	{
		$this['editForm']->setDefaults([
			'locale' => $this->localeService->defaultLocaleId
		]);
		$this->setView('edit');
	}

	/**
	 * Editace obsahu
	 * @param int $id
	 * @throws BadRequestException
	 */
	public function actionEdit(int $id): void
	{
		$this->content = $this->orm->content->getById($id);
		if (!$this->content) {
			$this->error();
		}
	}

	public function renderEdit(): void
	{
		$content = $this->content;
		$this->addBreadcrumbLinkUntranslated($content->name);

		$form = $this['editForm'];
		$form->setDefaults($content->toArray(ToArrayConverter::RELATIONSHIP_AS_ID));
		if ($content->background) {
			$form['background']->setDefaultValue((new Color($content->background))->rgb);
		}
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

		$form->addText('title', 'webManager.web.content.contentTitle')
			->addRule(Form::MAX_LENGTH, null, 150)
			->setRequired(false);

		$form->addTextArea('keywords', 'webManager.web.content.keywords');

		$form->addImageUpload('image', 'webManager.web.content.photo', 'webManager.web.content.photoDelete')
			->setNamespace('content')
			->setPreview('300x100');

		$form->addColor('background', 'webManager.web.content.background');

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
	public function editFormSucceeded(Form $form, ArrayHash $values): void
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
			$content->background = $values->background;
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
	 * @throws \Ublaboo\DataGrid\Exception\DataGridException
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

		if ($this->editConst) {
			$grid->addColumnText('const', 'webManager.web.content.const')
				->setSortable()
				->setFilterText();
		}

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
