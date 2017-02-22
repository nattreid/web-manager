<?php

namespace NAttreid\WebManager\Model\Content;

use NAttreid\Cms\Model\Locale\Locale;
use Nette\Utils\Strings;
use Nextras\Dbal\InvalidArgumentException;
use Nextras\Dbal\UniqueConstraintViolationException;
use Nextras\Orm\Entity\Entity;

/**
 * Content
 *
 * @property int $id {primary}
 * @property string $name
 * @property string $const
 * @property Locale $locale {m:1 Locale, oneSided=true}
 * @property string|null $title
 * @property string|null $image
 * @property string|null $keywords
 * @property string|null $description
 * @property string|null $content
 *
 * @author Attreid <attreid@gmail.com>
 */
class Content extends Entity
{

	/**
	 * Nastavi konstanu
	 * @param string $const
	 * @throws InvalidArgumentException
	 * @throws UniqueConstraintViolationException
	 */
	public function setConst($const)
	{
		if (!$this->locale) {
			throw new InvalidArgumentException('Locale must be set before calling setConst');
		}
		if (Strings::match($const, '/[^A-Za-z0-9_]/')) {
			throw new InvalidArgumentException('Constant contains invalid characters');
		}

		/* @var $repository ContentRepository */
		$repository = $this->getRepository();
		$content = $repository->getByConst($const, $this->locale);
		if ($content !== null && $content !== $this) {
			throw new UniqueConstraintViolationException("Content with '$const' exists");
		}
		$this->const = $const;
	}

}
