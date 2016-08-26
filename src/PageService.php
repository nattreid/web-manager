<?php

namespace NAttreid\WebManager;

use NAttreid\WebManager\Model\Orm,
    Nextras\Orm\Model\Model,
    Nette\Application\IRouter,
    Nette\Application\Routers\Route,
    NAttreid\Utils\Strings,
    NAttreid\WebManager\Model\Page;

/**
 * Sluzba stranek
 *
 * @author Attreid <attreid@gmail.com>
 */
class PageService extends \Nette\Application\UI\Control {

    /** @var Orm */
    private $orm;

    /** @var string */
    private $defaultLink;

    /** @var string */
    private $pageLink;

    public function __construct($defaultLink, $pageLink, Model $orm) {
        $this->defaultLink = $defaultLink;
        $this->pageLink = $pageLink;
        $this->orm = $orm;
    }

    /**
     * Vytvori routy
     * @param IRouter $routes
     * @param string $defaultRoute
     * @param string $url
     * @param string $flag
     */
    public function createRoute(IRouter $routes, $url, $flag) {
        $routes[] = new PageRoute($url, $flag, $this->orm);

        $routes[] = new Route($url, $this->defaultRoute, $flag);
        $routes[] = new Route($url . 'index.php', $this->pageLink, Route::ONE_WAY);
        $routes[] = new Route($url . '<presenter>[/<action>]', $this->pageLink, $flag);
    }

    /**
     * Vrati stranku, presmeruje na HP nebo vyhodi chybu 404
     * @param string $url
     * @return Page
     */
    public function getPage($url) {
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

}
