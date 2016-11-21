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
 * @property int $group {default 0}
 * @property array|int[] $groups {virtual}
 * @property int|null $position
 *
 * @author Attreid <attreid@gmail.com>
 */
class Page extends Entity
{
	const
		MENU = 1,
		FOOTER = 2;

	/**
	 * Page constructor.
	 * @param int $id
	 */
	public function __construct()
	{
		parent::__construct();
		if (!isset($this->group)) {
			$this->group = 0;
		}
	}

	protected function setterGroups($value)
	{
		$this->group = 0;
		if (is_array($value)) {
			foreach ($value as $row) {
				$this->group |= $row;
			}
		} else {
			$this->group |= $value;
		}
		return $value;
	}

	protected function getterGroups()
	{
		$result = [];
		/* @var $repo PagesRepository */
		$repo = $this->getRepository();
		$groups = $repo->fetchPairsGroupById();
		foreach ($groups as $group => $name) {
			if (($this->group & $group) > 0) {
				$result[] = $group;
			}
		}
		return $result;
	}

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
