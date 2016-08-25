<?php

namespace NAttreid\WebManager\Front;

use NAttreid\Utils\Strings,
    App\Model\Orm;

/**
 * Stranky
 *
 * @author Attreid <attreid@gmail.com>
 */
class PagePresenter extends BasePresenter {

    /** @var Orm @inject */
    private $orm;

    public function __construct(Orm $orm) {
        $this->orm = $orm;
    }

    public function renderDefault($url) {
        Strings::ifEmpty($url, '');
        $page = $this->orm->pages->getByUrl($url);
        if (!$page) {
            if ($url == '') {
                $this->forward('Homepage:');
            }
            $this->error();
        }
        $this->template->page = $page;
    }

}
