<?php

namespace NAttreid\WebManager\Model;

use Nette\Utils\Strings;

/**
 * Content
 * 
 * @property int $id {primary}
 * @property string $name 
 * @property string $const
 * @property string|NULL $title
 * @property string|NULL $image
 * @property string|NULL $keywords
 * @property string|NULL $description
 * @property string|NULL $content
 * 
 * @author Attreid <attreid@gmail.com>
 */
class Content extends \Nextras\Orm\Entity\Entity {

    /**
     * Nastavi konstanu
     * @param string $const
     * @throws InvalidArgumentException
     * @throws UniqueConstraintViolationException
     */
    public function setConst($const) {
        if (Strings::match($const, '/[^A-Za-z0-9_]/')) {
            throw new InvalidArgumentException('Constant contains invalid characters');
        }

        $repository = $this->getRepository(); /* @var $repository ContentRepository */
        $content = $repository->getByConst($const);
        if ($content !== NULL && $content !== $this) {
            throw new UniqueConstraintViolationException("Content with '$const' exists");
        }
        $this->const = $const;
    }

}
