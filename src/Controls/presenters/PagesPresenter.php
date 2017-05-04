<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Presenters;

use InvalidArgumentException;
use NAttreid\Cms\LocaleService;
use NAttreid\Form\Form;
use NAttreid\Gallery\Control\Gallery;
use NAttreid\Gallery\Control\IGalleryFactory;
use NAttreid\Routing\RouterFactory;
use NAttreid\Security\Model\Acl\Acl;
use NAttreid\Utils\Strings;
use NAttreid\WebManager\Components\ILinksFactory;
use NAttreid\WebManager\Components\Links;
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

	private const DEFAULT_TAB = 'page';

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

	/** @var ILinksFactory */
	private $linksFactory;

	/** @var bool */
	private $editHomePage;

	/** @var bool */
	private $viewGallery;

	/** @var bool */
	private $viewLinks;

	public function __construct(Model $orm, LocaleService $localeService, PageService $pageService, RouterFactory $routerFactory, IGalleryFactory $galleryFactory, ILinksFactory $linksFactory)
	{
		parent::__construct();
		$this->orm = $orm;
		$this->localeService = $localeService;
		$this->pageService = $pageService;
		$this->routerFactory = $routerFactory;
		$this->galleryFactory = $galleryFactory;
		$this->linksFactory = $linksFactory;
	}

	protected function startup(): void
	{
		parent::startup();
		$this->editHomePage = $this->user->isAllowed('webManager.web.pages.homePage', Acl::PRIVILEGE_EDIT);
		$this->viewGallery = $this->user->isAllowed('webManager.web.pages.gallery', Acl::PRIVILEGE_VIEW);
		$this->viewLinks = $this->user->isAllowed('webManager.web.pages.links', Acl::PRIVILEGE_VIEW);
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
	public function handleSort(int $item_id = null, $prev_id = null, $next_id = null, $parent_id = null): void
	{
		if ($this->isAjax()) {
			$page = $this->orm->pages->getById($item_id);
			if (!$page->isHomePage) {
				$page->parent = $parent_id;
				$this->orm->persistAndFlush($page);

				$this->orm->pages->changeSort($item_id, $prev_id, $next_id, $this->locale);
			}
		} else {
			$this->terminate();
		}
	}

	protected function beforeRender(): void
	{
		parent::beforeRender();
		$this->template->viewGallery = $this->viewGallery;
		$this->template->viewLinks = $this->viewLinks;
		$this->template->tab = self::DEFAULT_TAB;
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
		$this->template->viewLinks = false;
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

	public function renderEdit(string $tab = null): void
	{
		$page = $this->page;
		$this->addBreadcrumbLinkUntranslated($page->name);

		$form = $this['editForm'];
		$form->setDefaults($page->toArray($page::TO_ARRAY_RELATIONSHIP_AS_ID));
		if ($page->isHomePage) {
			$form['homePage']->setDefaultValue(true);
		}

		if ($tab !== null) {
			$this->template->tab = $tab;
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

	protected function createComponentLinks(): Links
	{
		$control = $this->linksFactory->create();
		$control->setPage($this->page);
		return $control;
	}

	/**
	 * Editace stranky
	 * @return Form
	 */
	protected function createComponentEditForm(): Form
	{
		$form = $this->formFactory->create();

		$form->addHidden('parent');

		$homePage = $form->addCheckbox('homePage', 'webManager.web.pages.homePage')
			->setDisabled(!$this->editHomePage);
		$homePage->addCondition($form::EQUAL, false)
			->toggle('page-name')
			->toggle('page-url')
			->toggle('page-views');
		if ($homePage->isDisabled()) {
			$homePage->setOption('class', 'hidden');
		}

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

		$form->addText('title', 'webManager.web.pages.pageTitle');

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

		if (($this->editHomePage ?
			$values->homePage
			: ($isNew ? false : $page->isHomePage))
		) {
			$page->parent = null;
			$values->url = null;
			$values->views = [];
			$values->name = $this->translate('webManager.web.pages.homePage');
		} else {
			$page->parent = $values->parent;
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

		$grid->allowRowsAction('add', function (Page $page) {
			return !$page->isHomePage;
		});

		return $grid;
	}

	public function getChildren(int $id): ICollection
	{
		$page = $this->orm->pages->getById($id);
		return $page->children->get();
	}

}
