<?php

namespace NAttreid\WebManager\Model\PagesViews;

use Kdyby\Translation\Translator;
use NAttreid\WebManager\Model\Pages\Page;
use Nextras\Orm\Entity\Entity;
use Nextras\Orm\Relationships\ManyHasMany;

/**
 * PageView
 *
 * @property int $id {primary}
 * @property ManyHasMany|Page[] $pages {m:n Page::$views}
 * @property string $name
 * @property string $translatedName {virtual}
 * @property string $untranslatedName {virtual}
 *
 * @author Attreid <attreid@gmail.com>
 */
class PageView extends Entity
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
		return 'webManager.web.pages.views.' . $this->name;
	}
}
