<?php

namespace NAttreid\WebManager;

use NAttreid\Utils\Strings;
use NAttreid\WebManager\Model\Content;
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
 * Sluzba Manageru
 *
 * @property-read string $pageLink
 *
 * @author Attreid <attreid@gmail.com>
 */
class Service
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

	public function __construct($defaultLink, $pageLink, $module, Model $orm)
	{
		$this->defaultLink = $defaultLink;
		$this->pageLink = $pageLink;
		$this->module = $module;
		$this->orm = $orm;
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
			NULL => [
				Route::FILTER_IN => function ($params) {
					if ($this->orm->pages->exists($params['url'])) {
						return $params;
					}
					return NULL;
				}
			],
		]);

		$routes[] = new Route($url, $this->defaultLink);
		$routes[] = new Route($url . 'index.php', $this->pageLink, Route::ONE_WAY);
		$routes[] = new Route($url . '<presenter>[/<action>]', $this->pageLink);
	}

	/**
	 * Vrati stranku
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
	 * @param bool $withHome
	 * @return Page[]|ICollection
	 */
	public function getPages($withHome = FALSE)
	{
		return $this->orm->pages->findAll($withHome);
	}

	/**
	 * Vrati text
	 * @param $const
	 * @param $locale
	 * @return Content
	 */
	public function getContent($const, $locale)
	{
		return $this->orm->content->getByConst($const, $locale);
	}

}
