<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Components;

use Kdyby\Translation\Translator;
use NAttreid\Cms\Factories\DataGridFactory;
use NAttreid\Cms\Factories\FormFactory;
use NAttreid\Form\Form;
use NAttreid\WebManager\Model\Orm;
use NAttreid\WebManager\Model\Pages\Page;
use NAttreid\WebManager\Model\PagesLinks\PageLink;
use NAttreid\WebManager\Model\PagesLinksGroups\PageLinkGroup;
use Nette\Application\UI\Control;
use Nette\Utils\ArrayHash;
use Nextras\Application\UI\SecuredLinksControlTrait;
use Nextras\Orm\Model\Model;
use Ublaboo\DataGrid\DataGrid;

/**
 * Class Links
 *
 * @author Attreid <attreid@gmail.com>
 */
class Links extends Control
{
	use SecuredLinksControlTrait;

	/** @var Orm */
	private $orm;

	/** @var DataGridFactory */
	private $gridFactory;

	/** @var FormFactory */
	private $formFactory;

	/** @var Translator */
	private $translator;

	/** @var string */
	private $latte = 'default';

	/** @var Page */
	private $page;

	/** @var string @persistent */
	public $groupId;

	public function __construct(Model $orm, DataGridFactory $gridFactory, FormFactory $formFactory, Translator $translator)
	{
		parent::__construct();
		$this->orm = $orm;
		$this->gridFactory = $gridFactory;
		$this->formFactory = $formFactory;
		$this->translator = $translator;
	}

	public function setPage(Page $page)
	{
		$this->page = $page;
	}

	private function checkAjax()
	{
		if (!$this->presenter->isAjax()) {
			$this->presenter->terminate();
		}
	}

	/* ********************************************************************************************* */
	/* ****************************************** Groups ******************************************* */

	/** @secured */
	public function handleLinkGroupAdd()
	{
		$this->checkAjax();
		$this->latte = 'group';
		$this->redrawControl();
	}

	/** @secured */
	public function handleLinkGroupEdit(int $id)
	{
		$this->checkAjax();
		$this->latte = 'group';
		$this->groupId = $id;

		$group = $this->orm->pagesLinksGroups->getById($id);
		if (!$group) {
			$this->presenter->error();
		}

		$this['linkGroupForm']->setDefaults($group->toArray($group::TO_ARRAY_RELATIONSHIP_AS_ID));
		$this->redrawControl();
	}

	/** @secured */
	public function handleLinkGroupDelete($id)
	{
		$this->checkAjax();
		$groups = $this->orm->pagesLinksGroups->findById($id);
		foreach ($groups as $group) {
			$this->orm->remove($group);
		}
		$this->orm->flush();
		$this['linkGroups']->reload();
	}

	/** @secured */
	public function handleLinkGroupSort(int $item_id = null, $prev_id = null, $next_id = null): void
	{
		$this->checkAjax();
		$this->orm->pagesLinksGroups->changeSort($item_id, $prev_id, $next_id);
	}

	/** @secured */
	public function handleLinkGroupVisibility(int $id)
	{
		$this->checkAjax();
		$group = $this->orm->pagesLinksGroups->getById($id);
		$group->visible = !$group->visible;
		$this->orm->persistAndFlush($group);
		$this['linkGroups']->redrawItem($id);
	}

	/** @secured */
	public function handleBack()
	{
		$this->checkAjax();
		$this->latte = 'default';
		$this->redrawControl();
	}

	protected function createComponentLinkGroupForm()
	{
		$form = $this->formFactory->create();
		$form->setAjaxRequest();

		$form->addHidden('id');

		$form->addTextArea('name', 'webManager.web.pages.linkGroup.name')
			->setRequired()
			->setAttribute('class', 'lineCKedit');

		$form->addSubmit('save', 'form.save');
		$form->addLink('back', 'form.back', $this->link('back'))
			->setAjaxRequest()
			->setAttribute('data-ajax-off', 'history');

		$form->onSuccess[] = [$this, 'saveLinkGroup'];
		$form->onError[] = function () {
			$this->latte = 'group';
			$this->redrawControl();
		};

		return $form;
	}

	public function saveLinkGroup(Form $form, ArrayHash $values)
	{
		$this->checkAjax();

		if ($values->id) {
			$group = $this->orm->pagesLinksGroups->getById($values->id);
		} else {
			$group = new PageLinkGroup;
			$this->orm->pagesLinksGroups->attach($group);
		}
		$group->name = $values->name;
		$group->page = $this->page;

		$this->orm->persistAndFlush($group);

		$this->handleBack();
	}

	protected function createComponentLinkGroups(): DataGrid
	{
		$grid = $this->gridFactory->create();
		$grid->setDataSource($this->orm->pagesLinksGroups->findAll());
		$grid->setSortable();
		$grid->setSortableHandler('links-linkGroupSort!');
		$grid->setRefreshUrl(false);
		$grid->addToolbarButton('linkGroupAdd!', 'webManager.web.pages.linkGroup.add')
			->setClass('btn btn-xs btn-default ajax');

		$grid->addColumnText('name', 'default.name')
			->setTemplate(__DIR__ . '/name.latte');

		$grid->addAction('visibility', null, 'linkGroupVisibility!')
			->setClass(function (PageLinkGroup $group) {
				return $group->visible ? 'btn btn-xs btn-success ajax' : 'btn btn-xs btn-default ajax';
			})
			->setIcon(function (PageLinkGroup $group) {
				return $group->visible ? 'eye' : 'eye-slash';
			});

		$grid->addAction('edit', null, 'linkGroupEdit!')
			->setIcon('pencil')
			->setTitle('default.edit')
			->setClass('btn btn-xs btn-default ajax');

		$grid->addAction('delete', null, 'linkGroupDelete!')
			->setIcon('trash')
			->setTitle('default.delete')
			->setClass('btn btn-xs btn-danger ajax')
			->setConfirm(function (PageLinkGroup $group) {
				return $this->translator->translate('default.confirmDelete', 1, ['name' => $group->name]);
			});

		$grid->addGroupAction('default.delete')->onSelect[] = [$this, 'handleLinkGroupDelete'];

		return $grid;
	}

	/* ********************************************************************************************* */
	/* ******************************************* Links ******************************************* */

	/** @secured */
	public function handleLinkAdd()
	{
		$this->checkAjax();
		$this->latte = 'link';
		$this->redrawControl();
	}

	/** @secured */
	public function handleLinkEdit(int $id)
	{
		$this->checkAjax();
		$this->latte = 'link';

		$link = $this->orm->pagesLinks->getById($id);
		if (!$link) {
			$this->presenter->error();
		}

		$form = $this['linkForm'];
		$form->setDefaults($link->toArray($link::TO_ARRAY_RELATIONSHIP_AS_ID));
		$this->redrawControl();
	}

	/** @secured */
	public function handleLinkDelete($id)
	{
		$this->checkAjax();
		$links = $this->orm->pagesLinks->findById($id);
		foreach ($links as $link) {
			$this->orm->remove($link);
		}
		$this->orm->flush();
		$this['links']->reload();
	}

	/** @secured */
	public function handleLinkSort(int $item_id = null, $prev_id = null, $next_id = null): void
	{
		$this->checkAjax();
		$this->orm->pagesLinks->changeSort($item_id, $prev_id, $next_id);
	}

	/** @secured */
	public function handleLinkVisibility(int $id)
	{
		$this->checkAjax();
		$link = $this->orm->pagesLinks->getById($id);
		$link->visible = !$link->visible;
		$this->orm->persistAndFlush($link);

		$this['links']->redrawItem($id);
	}

	protected function createComponentLinkForm()
	{
		$form = $this->formFactory->create();
		$form->setAjaxRequest();

		$form->addHidden('id');
		$form->addHidden('group', $this->groupId);

		$form->addTextArea('name', 'webManager.web.pages.link.name')
			->setRequired()
			->setAttribute('class', 'lineCKedit');

		$form->addText('url', 'webManager.web.pages.link.url')
			->addRule($form::URL)
			->setRequired();

		$form->addImageUpload('image', 'webManager.web.pages.link.image', 'default.delete')
			->setPreview('300x200')
			->setRequired()
			->setNamespace('web-manager-links');

		$form->addTextArea('content', 'webManager.web.pages.link.content')
			->setAttribute('class', 'lineCKedit');

		$form->addSubmit('save', 'form.save');
		$form->addLink('back', 'form.back', $this->link('linkGroupEdit', [$this->groupId]))
			->setAjaxRequest()
			->setAttribute('data-ajax-off', 'history');

		$form->onSuccess[] = [$this, 'saveLink'];
		$form->onError[] = function () {
			$this->latte = 'link';
			$this->redrawControl();
		};

		return $form;
	}

	public function saveLink(Form $form, ArrayHash $values)
	{
		$this->checkAjax();
		if ($values->id) {
			$link = $this->orm->pagesLinks->getById($values->id);
		} else {
			$link = new PageLink;
			$this->orm->pagesLinks->attach($link);
		}
		$link->name = $values->name;
		$link->url = $values->url;
		$link->image = $values->image;
		$link->content = $values->content;
		$link->group = $values->group;

		$this->orm->persistAndFlush($link);

		$this->handleLinkGroupEdit((int) $values->group);
	}

	protected function createComponentLinks(): DataGrid
	{
		$grid = $this->gridFactory->create();
		$grid->setDataSource($this->orm->pagesLinks->findByGroup((int) $this->groupId));
		$grid->setSortable();
		$grid->setSortableHandler('links-linkSort!');
		$grid->setRefreshUrl(false);
		$grid->addToolbarButton('linkAdd!', 'webManager.web.pages.link.add')
			->setClass('btn btn-xs btn-default ajax');

		$grid->addColumnText('name', 'default.name')
			->setTemplate(__DIR__ . '/name.latte');

		$grid->addAction('visibility', null, 'linkVisibility!')
			->setClass(function (PageLink $link) {
				return $link->visible ? 'btn btn-xs btn-success ajax' : 'btn btn-xs btn-default ajax';
			})
			->setIcon(function (PageLink $link) {
				return $link->visible ? 'eye' : 'eye-slash';
			});

		$grid->addAction('edit', null, 'linkEdit!')
			->setIcon('pencil')
			->setTitle('default.edit')
			->setClass('btn btn-xs btn-default ajax');

		$grid->addAction('delete', null, 'linkDelete!')
			->setIcon('trash')
			->setTitle('default.delete')
			->setClass('btn btn-xs btn-danger ajax')
			->setConfirm(function (PageLink $link) {
				return $this->translator->translate('default.confirmDelete', 1, ['name' => $link->name]);
			});

		$grid->addGroupAction('default.delete')->onSelect[] = [$this, 'handleLinkDelete'];

		return $grid;
	}

	public function render(): void
	{
		$this->template->setFile(__DIR__ . '/' . $this->latte . '.latte');

		$this->template->render();
	}
}

interface ILinksFactory
{
	public function create(): Links;
}