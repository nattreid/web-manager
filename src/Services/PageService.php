<?php

declare(strict_types=1);

namespace NAttreid\WebManager\Services;

use Kdyby\Translation\Translator;
use NAttreid\WebManager\Model\Content\Content;
use NAttreid\WebManager\Model\Orm;
use NAttreid\WebManager\Model\Pages\Page;
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

	public function __construct(string $defaultLink, string $pageLink, string $module, Model $orm, Translator $translator)
	{
		$this->defaultLink = $defaultLink;
		$this->pageLink = $pageLink;
		$this->module = $module;
		$this->orm = $orm;
		$this->translator = $translator;
	}

	/**
	 * Vytvori routy
	 * @param IRouter $routes
	 * @param string $url
	 */
	public function createRoute(IRouter $routes, string $url)
	{
		list($presenter, $action) = explode(':', $this->pageLink);

		$routes[] = new Route($url . '[<url .*>]', [
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
	public function getPage(string $url = null): Page
	{
		$page = $this->orm->pages->getByUrl($url ?? '', $this->translator->getLocale());
		if (!$page) {
			throw new BadRequestException(null, IResponse::S404_NOT_FOUND);
		}
		return $page;
	}

	/**
	 * @return string
	 */
	public function getPageLink(): string
	{
		return ':' . $this->module . ':' . $this->pageLink;
	}

	/**
	 * Vrati stranky bez HP
	 * @return Page[]|ICollection
	 */
	public function findPages(): ICollection
	{
		return $this->orm->pages->findByLocale($this->translator->getLocale());
	}

	/**
	 * Vrati stranky v menu
	 * @return Page[]|ICollection
	 */
	public function findMenuPages(): ICollection
	{
		return $this->orm->pages->findMenu($this->translator->getLocale());
	}

	/**
	 * Vrati stranky v paticce
	 * @return Page[]|ICollection
	 */
	public function findFooterPages(): ICollection
	{
		return $this->orm->pages->findFooter($this->translator->getLocale());
	}

	/**
	 * Vrati text
	 * @param string $const
	 * @return Content|null
	 */
	public function getContent(string $const)
	{
		return $this->orm->content->getByConst($const, $this->translator->getLocale());
	}
}
