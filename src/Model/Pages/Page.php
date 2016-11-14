<?php

namespace NAttreid\WebManager\Model;

use NAttreid\Crm\Model\Locale;
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
 * @property Locale $locale {m:1 Locale, oneSided=true}
 * @property string $title
 * @property string|null $image
 * @property string|null $keywords
 * @property string|null $description
 * @property string|null $content
 * @property int|null $position
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
		if (!$this->locale) {
			throw new InvalidArgumentException('Locale must be set before calling setUrl');
		}
		if (Strings::match($url, '/[^A-Za-z0-9_-]/')) {
			throw new InvalidArgumentException('URL contains invalid characters');
		}

		/* @var $repository PagesRepository */
		$repository = $this->getRepository();
		$page = $repository->getByUrl($url, $this->locale);
		if ($page !== null && $page !== $this) {
			throw new UniqueConstraintViolationException("Page with '$url' exists");
		}
		$this->url = $url;
	}

	protected function onBeforeInsert()
	{
		if ($this->position === null) {
			/* @var $repo PagesRepository */
			$repo = $this->getRepository();
			$this->position = $repo->getMaxPosition() + 1;
		}
	}

}
