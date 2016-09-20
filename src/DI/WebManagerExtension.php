<?php

namespace NAttreid\Analytics\DI;

use NAttreid\AppManager\AppManager;
use NAttreid\Crm\DI\ModuleExtension;
use NAttreid\Menu\Menu;
use NAttreid\WebManager\IPageMenuFactory;
use NAttreid\WebManager\PageService;
use Nette\DI\Statement;
use Nette\InvalidStateException;
use Nextras\Orm\Model\Model;

/**
 * Rozsireni
 *
 * @author Attreid <attreid@gmail.com>
 */
class WebManagerExtension extends ModuleExtension
{

	protected $namespace = 'webManager';
	protected $dir = __DIR__;
	protected $package = 'NAttreid\\';

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->loadFromFile($this->dir . '/default.neon'), $this->config);

		if ($config['homepage'] === NULL) {
			throw new InvalidStateException("WebManager: 'homepage' does not set in config.neon");
		}
		if ($config['page'] === NULL) {
			throw new InvalidStateException("WebManager: 'page' does not set in config.neon");
		}

		$builder->addDefinition($this->prefix('pageService'))
			->setClass(PageService::class)
			->setArguments([$config['homepage'], $config['page']]);

		$builder->addDefinition($this->prefix('menu'))
			->setImplement(IPageMenuFactory::class)
			->setFactory(Menu::class);
	}

	public function beforeCompile()
	{
		parent::beforeCompile();
		$builder = $this->getContainerBuilder();

		$app = $builder->getByType(AppManager::class);
		$builder->getDefinition($app)
			->addSetup(new Statement('$app->onInvalidateCache[] = function() {?->pages->cleanCache();}', ['@' . Model::class]));
	}

}
