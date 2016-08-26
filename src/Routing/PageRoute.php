<?php

namespace NAttreid\WebManager\Routing;

use NAttreid\WebManager\Model\Orm,
    \Nextras\Orm\Model\Model;

/**
 * Routa stranek
 *
 * @author Attreid <attreid@gmail.com>
 */
class PageRoute extends \NAttreid\Routing\Route {

    /** @var Orm */
    private $orm;

    public function __construct($url, $flag, $pageLink, Model $orm) {
        $this->orm = $orm;
        parent::__construct($url . '[<url>]', $pageLink, $flag);
    }

    public function in($url) {
        if ($this->orm->pages->exists($url)) {
            $this->parameters->url = $url;
            return TRUE;
        }
    }

    public function out() {
        $this->addToSlug($this->parameters->url);
    }

}
