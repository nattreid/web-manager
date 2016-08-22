<?php

namespace NAttreid\WebManager\Model;

/**
 * Contents Repository
 *
 * @author Attreid <attreid@gmail.com>
 */
class ContentsRepository extends \NAttreid\Orm\Repository {

    public static function getEntityClassNames() {
        return [Content::class];
    }

    /**
     * Vrati pouze obsah podle konstanty
     * @param string $const
     * @return string|FALSE
     */
    public function getContent($const) {
        $content = $this->getByConst($const);
        if ($content) {
            return $content->content;
        } else {
            return FALSE;
        }
    }

    /**
     * Vrati obsah podle konstanty
     * @param string $const
     * @return Content
     */
    public function getByConst($const) {
        return $this->getBy(['const' => $const]);
    }

}
