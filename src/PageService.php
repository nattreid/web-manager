<?php

namespace NAttreid\WebManager;

use NAttreid\Menu\Menu;
use NAttreid\Utils\Strings;
use NAttreid\WebManager\Model\Orm;
use NAttreid\WebManager\Model\Page;
use Nette\Application\BadRequestException;
use Nette\Application\IRouter;
use Nette\Application\Routers\Route;
use Nette\Http\IResponse;
use Nette\SmartObject;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Model\Model;

/**
 * Sluzba stranek
 *
 * @property-read string $pageLink
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

	/** @var string */
	private $module;

	/** @var IPageMenuFactory */
	private $menuFactory;

	public function __construct($defaultLink, $pageLink, $module, Model $orm, IPageMenuFactory $menuFactory)
	{
		$this->defaultLink = $defaultLink;
		$this->pageLink = $pageLink;
		$this->module = $module;
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
		list($presenter, $action) = explode(':', $this->pageLink);

		$routes[] = new Route($url . '[<url>]', [
			'presenter' => $presenter,
			'action' => $action,
			'url' => [
				Route::FILTER_IN => function ($url) {
					if ($this->orm->pages->exists($url)) {
						return $url;
					}
				}
			],
		]);

		$routes[] = new Route($url, $this->defaultLink);
		$routes[] = new Route($url . 'index.php', $this->pageLink, Route::ONE_WAY);
		$routes[] = new Route($url . '<presenter>[/<action>]', $this->pageLink);
	}

	/**
	 * Vrati stranku, presmeruje na HP nebo vyhodi chybu 404
	 * @param string $url
	 * @param string $locale
	 * @return Page
	 * @throws BadRequestException
	 */
	public function getPage($url, $locale)
	{
		Strings::ifEmpty($url, '');
		$page = $this->orm->pages->getByUrl($url, $locale);
		if (!$page) {
			throw new BadRequestException(NULL, IResponse::S404_NOT_FOUND);
		}
		return $page;
	}

	/**
	 * @return string
	 */
	public function getPageLink()
	{
		return ':' . $this->module . ':' . $this->pageLink;
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
