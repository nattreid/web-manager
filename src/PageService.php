<?php

namespace NAttreid\WebManager;

use NAttreid\Menu\Menu;
use NAttreid\Utils\Strings;
use NAttreid\WebManager\Model\Orm;
use NAttreid\WebManager\Model\Page;
use Nette\Application\IRouter;
use Nette\Application\Routers\Route;
use Nette\SmartObject;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Model\Model;

/**
 * Sluzba stranek
 *
 * @author Attreid <attreid@gmail.com>
 */
class PageService
{
	use SmartObject;

	/** @var Orm */
	private $orm;

	/** @var string */
	private $defaultLink;

	/** @var string */
	private $pageLink;

	/** @var IPageMenuFactory */
	private $menuFactory;

	public function __construct($defaultLink, $pageLink, Model $orm, IPageMenuFactory $menuFactory)
	{
		$this->defaultLink = $defaultLink;
		$this->pageLink = $pageLink;
		$this->orm = $orm;
		$this->menuFactory = $menuFactory;
	}

	/**
	 * Vytvori routy
	 * @param IRouter $routes
	 * @param string $url
	 */
	public function createRoute(IRouter $routes, $url)
	{
		$routes[] = new Routing\PageRoute($url, $this->pageLink, $this->orm);

		$routes[] = new Route($url, $this->defaultLink);
		$routes[] = new Route($url . 'index.php', $this->pageLink, Route::ONE_WAY);
		$routes[] = new Route($url . '<presenter>[/<action>]', $this->pageLink);
	}

	/**
	 * Vrati stranku, presmeruje na HP nebo vyhodi chybu 404
	 * @param string $url
	 * @return Page
	 */
	public function getPage($url)
	{
		Strings::ifEmpty($url, '');
		$page = $this->orm->pages->getByUrl($url);
		if (!$page) {
			if ($url == '') {
				$this->presenter->redirect($this->defaultLink);
			} else {
				$this->presenter->error();
			}
		}
		return $page;
	}

	/**
	 * Vrati stranky krome Homepage
	 * @return Page[]|ICollection
	 */
	public function getPages()
	{
		return $this->orm->pages->findPages();
	}

	/**
	 * Vrati menu
	 * @return Menu
	 */
	public function createMenu()
	{
		$menu = $this->menuFactory->create();
		foreach ($this->getPages() as $page) {
			$menu->addLink($page->name, $page->url);
		}

		return $menu;
	}

}
