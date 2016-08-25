<?php

namespace NAttreid\WebManager\Routing;

use NAttreid\WebManager\Model\Orm,
    Nextras\Orm\Model\Model,
    Nette\Application\IRouter,
    Nette\Application\Routers\Route;

/**
 * Vytvori routy
 *
 * @author Attreid <attreid@gmail.com>
 */
class RouteFactory {

    /** @var Orm */
    private $orm;

    public function __construct(Model $orm) {
        $this->orm = $orm;
    }

    public function create(IRouter $routes, $defaultRoute, $url, $flag) {
        $routes[] = new PageRoute($url, $flag, $this->orm);

        $routes[] = new Route($url, $defaultRoute, $flag);
        $routes[] = new Route($url . 'index.php', 'WebManagerExt:Page:default', Route::ONE_WAY);
        $routes[] = new Route($url . '<presenter>[/<action>]', 'WebManagerExt:Page:default', $flag);
    }

}
