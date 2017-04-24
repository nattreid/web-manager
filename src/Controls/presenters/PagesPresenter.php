<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Presenters;

use InvalidArgumentException;
use NAttreid\Cms\LocaleService;
use NAttreid\Form\Form;
use NAttreid\Gallery\Control\Gallery;
use NAttreid\Gallery\Control\IGalleryFactory;
use NAttreid\Routing\RouterFactory;
use NAttreid\Utils\Strings;
use NAttreid\WebManager\Model\Orm;
use NAttreid\WebManager\Model\Pages\Page;
use NAttreid\WebManager\Services\PageService;
use Nette\Utils\ArrayHash;
use Nextras\Dbal\UniqueConstraintViolationException;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Model\Model;
use Ublaboo\DataGrid\DataGrid;

/**
 * Stranky
 *
 * @author Attreid <attreid@gmail.com>
 */
class PagesPresenter extends BasePresenter
{

	/** @var Orm */
	private $orm;

	/** @var Page */
	private $page;

	/** @var LocaleService */
	private $localeService;

	/** @var PageService */
	private $pageService;

	/** @var RouterFactory */
	private $routerFactory;

	/** @var IGalleryFactory */
	private $galleryFactory;

	public function __construct(Model $orm, LocaleService $localeService, PageService $pageService, RouterFactory $routerFactory, IGalleryFactory $galleryFactory)
	{
		parent::__construct();
		$this->orm = $orm;
		$this->localeService = $localeService;
		$this->pageService = $pageService;
		$this->routerFactory = $routerFactory;
		$this->galleryFactory = $galleryFactory;
	}

	public function handleBack(string $backlink = null): void
	{
		$this->redirect('default');
	}

	/**
	 * Smazani stranky
	 * @param int $id
	 * @secured
	 */
	public function handleDelete(int $id): void
	{
		if ($this->isAjax()) {
			$page = $this->orm->pages->getById($id);
			$parent = $page->parent;
			$this->orm->pages->removeAndFlush($page);
			if ($parent !== null) {
				$this['list']->redrawItem($parent->id);
			} else {
				$this['list']->redrawControl();
			}
		} else {
			$this->terminate();
		}
	}

	/**
	 * Serazeni
	 * @param int $item_id
	 * @param int $prev_id
	 * @param int $next_id
	 * @param int $parent_id
	 */
	public function handleSort(int $item_id = null, int $prev_id = null, int $next_id = null, int $parent_id = null): void
	{
		if ($this->isAjax()) {
			$page = $this->orm->pages->getById($item_id);
			$page->parent = $parent_id;
			$this->orm->persistAndFlush($page);

			$this->orm->pages->changeSort($item_id, $prev_id, $next_id);
		} else {
			$this->terminate();
		}
	}

	/**
	 * Pridani stranky
	 */
	public function actionAdd(): void
	{
		$session = $this->getSession('cms/web-manager/pages');
		$gallery = $this['gallery'];
		$gallery->setStorage($session);
		$gallery->setNamespace('page');
	}

	/**
	 * Pridani stranky
	 * @param int $id
	 */
	public function renderAdd(int $id = null): void
	{
		$this['editForm']->setDefaults([
			'locale' => $this->localeService->defaultLocaleId,
			'parent' => $id
		]);
		$this->setView('edit');
	}

	/**
	 * Editace stranky
	 * @param int $id
	 */
	public function actionEdit(int $id): void
	{
		$this->page = $this->orm->pages->getById($id);
		if (!$this->page) {
			$this->error();
		}

		$gallery = $this['gallery'];
		$gallery->setStorage($this->orm->pagesGalleries);
		$gallery->setForeignKey('page', $this->page->id);
		$gallery->setNamespace('page/' . $this->page->url);
	}

	public function renderEdit(): void
	{
		$page = $this->page;
		$this->addBreadcrumbLinkUntranslated($page->name);

		$form = $this['editForm'];
		$form->setDefaults($page->toArray($page::TO_ARRAY_RELATIONSHIP_AS_ID));
		if ($page->url === '') {
			$form['homePage']->setDefaultValue(true);
		}
	}

	/**
	 * @return Gallery
	 */
	protected function createComponentGallery(): Gallery
	{
		$gallery = $this->galleryFactory->create();
		$gallery->getTranslator()->setLang($this->locale);
		return $gallery;
	}

	/**
	 * Editace stranky
	 * @return Form
	 */
	protected function createComponentEditForm(): Form
	{
		$form = $this->formFactory->create();

		$form->addHidden('parent');

		$form->addCheckbox('homePage', 'webManager.web.pages.homePage')
			->addCondition($form::EQUAL, false)
			->toggle('page-name')
			->toggle('page-url')
			->toggle('page-views');

		$form->addText('name', 'webManager.web.pages.name')
			->setOption('id', 'page-name')
			->addConditionOn($form['homePage'], $form::EQUAL, false)
			->setRequired();

		$form->addText('url', 'default.url')
			->setOption('id', 'page-url');

		$form->addSelectUntranslated('locale', 'webManager.web.pages.locale')
			->setItems($this->localeService->allowed);

		$form->addCheckboxListUntranslated('views', 'webManager.web.pages.views.title')
			->setItems($this->orm->pagesViews->fetchPairsById())
			->setOption('id', 'page-views');

		$form->addText('title', 'webManager.web.pages.pageTitle')
			->setRequired();

		$form->addTextArea('keywords', 'webManager.web.pages.keywords');

		$form->addImageUpload('image', 'webManager.web.pages.photo', 'webManager.web.pages.photoDelete')
			->setNamespace('pages')
			->setPreview('300x100');

		$form->addTextArea('description', 'webManager.web.pages.description');

		$form->addTextEditor('content', 'webManager.web.pages.content');

		$form->addSubmit('save', 'form.save');

		$form->addLink('back', 'form.back', $this->getBacklink());

		$form->onSuccess[] = [$this, 'editFormSucceeded'];

		return $form;
	}

	/**
	 * Ulozeni stranky
	 * @param Form $form
	 * @param ArrayHash $values
	 */
	public function editFormSucceeded(Form $form, ArrayHash $values): void
	{
		if ($this->page) {
			$page = $this->page;
			$isNew = false;
		} else {
			$page = new Page;
			$this->orm->pages->attach($page);
			$isNew = true;
		}

		if ($values->homePage) {
			$values->url = '';
			$values->views = [];
			$values->name = $this->translate('webManager.web.pages.homePage');
		} else {
			$values->url = $values->url ?: Strings::webalize($values->name);
		}

		$page->locale = $values->locale;
		try {
			$page->setUrl($values->url);
		} catch (UniqueConstraintViolationException $ex) {
			$form->addError('webManager.web.pages.urlExists');
			return;
		} catch (InvalidArgumentException $ex) {
			$form->addError('webManager.web.pages.urlContainsInvalidCharacters');
			return;
		}

		$page->name = $values->name;
		$page->parent = $values->parent;
		$page->title = $values->title;
		$page->keywords = $values->keywords;
		$page->image = $values->image;
		$page->description = $values->description;
		$page->content = $values->content;
		$page->views->set($values->views);

		if ($isNew) {
			$gallery = $this['gallery'];
			$gallery->changeNamespace('page/' . $page->url);
			$page->addImages($gallery->getImages());
			$gallery->clearTemp();
		}

		$this->orm->persistAndFlush($page);
		$this->restoreBacklink();


	}

	/**
	 * Seznam stranek
	 * @return DataGrid
	 */
	protected function createComponentList(): DataGrid
	{
		$grid = $this->dataGridFactory->create();
		$grid->setDataSource($this->orm->pages->findMain());
		$grid->setSortable();
		$grid->setTreeView([$this, 'getChildren'], 'hasChildren');

		$grid->addColumnLink('name', 'webManager.web.pages.name', $this->pageService->pageLink, null, ['url' => 'completeUrl', $this->routerFactory->variable => 'locale.name'])
			->setOpenInNewTab()
			->setFilterText();

		$grid->addColumnText('url', 'default.url')
			->setFilterText();

		$grid->addColumnText('locale', 'webManager.web.pages.locale')
			->setRenderer(function (Page $page) {
				return $page->locale->name;
			})
			->setFilterSelect($this->localeService->allowed);

		$grid->addColumnText('views', 'webManager.web.pages.views.title')
			->setRenderer(function (Page $row) {
				return implode(', ', $row->getViews());
			})
			->setFilterSelect(['' => 'form.none'] + $this->orm->pagesViews->fetchUntranslatedPairsById(), 'views.id')
			->setTranslateOptions();

		$grid->addAction('add', null)
			->setIcon('plus')
			->setTitle('webManager.web.pages.add');

		$grid->addAction('edit', null)
			->setIcon('pencil')
			->setTitle('default.edit');

		$grid->addAction('delete', null, 'delete!')
			->setIcon('trash')
			->setTitle('default.delete')
			->setClass('btn btn-xs btn-danger ajax')
			->setConfirm(function (Page $page) {
				return $this->translate('default.confirmDelete', 1, ['name' => $page->name]);
			});

		$grid->setDefaultFilter(['locale' => $this->localeService->defaultLocaleId]);

		return $grid;
	}

	public function getChildren(int $id): ICollection
	{
		$page = $this->orm->pages->getById($id);
		return $page->children->get();
	}

}
