<?php

namespace NAttreid\WebManager\Model;

use Kdyby\Translation\Translator;
use Nextras\Orm\Entity\Entity;

/**
 * PageGroup
 *
 * @property int $id {primary}
 * @property Page $page {m:1 Page::groups}
 * @property int $group
 * @property string $name {virtual}
 *
 * @author Attreid <attreid@gmail.com>
 */
class PageGroup extends Entity
{
	const
		MENU = 1,
		FOOTER = 2;

	public static $names = [
		PageGroup::MENU => 'webManager.web.pages.groups.menu',
		PageGroup::FOOTER => 'webManager.web.pages.groups.footer'
	];

	/** @var Translator */
	private $translator;

	public function injectTranslator(Translator $translator)
	{
		$this->translator = $translator;
	}

	protected function getterName($value)
	{
		return $this->translator->translate(self::$names[$value]);
	}
}
