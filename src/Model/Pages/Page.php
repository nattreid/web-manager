<?php

namespace NAttreid\WebManager\Model;

use Nette\InvalidArgumentException;
use Nette\Utils\Strings;
use Nextras\Dbal\UniqueConstraintViolationException;
use Nextras\Orm\Entity\Entity;

/**
 * Page
 *
 * @property int $id {primary}
 * @property string $name
 * @property string $url
 * @property string $title
 * @property string|NULL $image
 * @property string|NULL $keywords
 * @property string|NULL $description
 * @property string|NULL $content
 * @property int|NULL $position
 *
 * @author Attreid <attreid@gmail.com>
 */
class Page extends Entity
{

	/**
	 * Nastavi URL
	 * @param string $url
	 * @throws InvalidArgumentException
	 * @throws UniqueConstraintViolationException
	 */
	public function setUrl($url)
	{
		if (Strings::match($url, '/[^A-Za-z0-9_]/')) {
			throw new InvalidArgumentException('URL contains invalid characters');
		}

		/* @var $repository PagesRepository */
		$repository = $this->getRepository();
		$page = $repository->getByUrl($url);
		if ($page !== NULL && $page !== $this) {
			throw new UniqueConstraintViolationException("Page with '$url' exists");
		}
		$this->url = $url;
	}

	protected function onBeforeInsert()
	{
		if ($this->position === NULL) {
			/* @var $repo PagesRepository */
			$repo = $this->getRepository();
			$this->position = $repo->getMaxPosition() + 1;
		}
	}

}
