<?php

namespace NAttreid\Analytics\DI;

use NAttreid\AppManager\AppManager;
use NAttreid\Crm\DI\ModuleExtension;
use NAttreid\WebManager\Components\Footer;
use NAttreid\WebManager\Components\Header;
use NAttreid\WebManager\Components\IFooterFactory;
use NAttreid\WebManager\Components\IHeaderFactory;
use NAttreid\WebManager\Service;
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

		if ($config['homepage'] === null) {
			throw new InvalidStateException("WebManager: 'homepage' does not set in config.neon");
		}
		if ($config['page'] === null) {
			throw new InvalidStateException("WebManager: 'page' does not set in config.neon");
		}
		if ($config['module'] === null) {
			throw new InvalidStateException("WebManager: 'module' does not set in config.neon");
		}

		$builder->addDefinition($this->prefix('pageService'))
			->setClass(Service::class)
			->setArguments([$config['homepage'], $config['page'], $config['module']]);

		$builder->addDefinition($this->prefix('headerFactory'))
			->setImplement(IHeaderFactory::class)
			->setFactory(Header::class);

		$builder->addDefinition($this->prefix('footerFactory'))
			->setImplement(IFooterFactory::class)
			->setFactory(Footer::class);
	}

	public function beforeCompile()
	{
		parent::beforeCompile();
		$builder = $this->getContainerBuilder();

		$app = $builder->getByType(AppManager::class);
		$builder->getDefinition($app)
			->addSetup(new Statement('$service->onInvalidateCache[] = function() {?->pages->cleanCache();}', ['@' . Model::class]));
	}

}
