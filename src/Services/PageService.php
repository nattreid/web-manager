<?php

namespace NAttreid\WebManager\Services;

use Kdyby\Translation\Translator;
use NAttreid\Utils\Strings;
use NAttreid\WebManager\Components\Footer;
use NAttreid\WebManager\Components\Header;
use NAttreid\WebManager\Components\IFooterFactory;
use NAttreid\WebManager\Components\IHeaderFactory;
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
 * Sluzba obsahu manageru
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

	/** @var Translator */
	private $translator;

	/** @var IHeaderFactory */
	private $headerFactory;

	/** @var IFooterFactory */
	private $footerFactory;

	public function __construct($defaultLink, $pageLink, $module, Model $orm, Translator $translator, IHeaderFactory $headerFactory, IFooterFactory $footerFactory)
	{
		$this->defaultLink = $defaultLink;
		$this->pageLink = $pageLink;
		$this->module = $module;
		$this->orm = $orm;
		$this->translator = $translator;
		$this->headerFactory = $headerFactory;
		$this->footerFactory = $footerFactory;
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
			null => [
				Route::FILTER_IN => function ($params) {
					if ($this->orm->pages->exists($params['url'])) {
						return $params;
					}
					return null;
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
	 * @return Page
	 * @throws BadRequestException
	 */
	public function getPage($url)
	{
		Strings::ifEmpty($url, '');
		$page = $this->orm->pages->getByUrl($url, $this->translator->getLocale());
		if (!$page) {
			throw new BadRequestException(null, IResponse::S404_NOT_FOUND);
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
	 * Vrati stranky
	 * @return Page[]|ICollection
	 */
	public function findPages()
	{
		return $this->orm->pages->findByLocale($this->translator->getLocale());
	}

	/**
	 * Vrati stranky v menu
	 * @return Page[]|ICollection
	 */
	public function findMenuPages()
	{
		return $this->orm->pages->findMenu($this->translator->getLocale());
	}

	/**
	 * Vrati stranky v paticce
	 * @return Page[]|ICollection
	 */
	public function findFooterPages()
	{
		return $this->orm->pages->findFooter($this->translator->getLocale());
	}

	/**
	 * Vrati text
	 * @param $const
	 * @return Pages
	 */
	public function getContent($const)
	{
		return $this->orm->content->getByConst($const, $this->translator->getLocale());
	}

	/**
	 * @return Header
	 */
	public function createHeader()
	{
		return $this->headerFactory->create();
	}

	/**
	 * @return Footer
	 */
	public function createFooter()
	{
		return $this->footerFactory->create();
	}

}
