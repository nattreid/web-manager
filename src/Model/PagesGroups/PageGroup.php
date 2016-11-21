<?php

namespace NAttreid\WebManager\Model;

use Kdyby\Translation\Translator;
use Nextras\Orm\Entity\Entity;
use Nextras\Orm\Relationships\ManyHasMany;

/**
 * PageGroup
 *
 * @property int $id {primary}
 * @property ManyHasMany|Page[] $pages {m:n Page::$groups}
 * @property string $name
 * @property string $translatedName {virtual}
 * @property string $untranslatedName {virtual}
 *
 * @author Attreid <attreid@gmail.com>
 */
class PageGroup extends Entity
{
	/** @var Translator */
	private $translator;

	public function injectTranslator(Translator $translator)
	{
		$this->translator = $translator;
	}

	protected function getterTranslatedName()
	{
		return $this->translator->translate($this->untranslatedName);
	}

	protected function getterUntranslatedName()
	{
		return 'webManager.web.pages.groups.' . $this->name;
	}
}
