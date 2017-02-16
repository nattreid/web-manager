<?php

namespace NAttreid\WebManager\DI;

use NAttreid\AppManager\AppManager;
use NAttreid\Cms\DI\ModuleExtension;
use NAttreid\WebManager\Services\Hooks\HookFactory;
use NAttreid\WebManager\Services\Hooks\HookService;
use NAttreid\WebManager\Services\PageService;
use Nette\DI\ServiceDefinition;
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
			->setClass(PageService::class)
			->setArguments([$config['homepage'], $config['page'], $config['module']]);

		$builder->addDefinition($this->prefix('hookService'))
			->setClass(HookService::class);
	}

	public function beforeCompile()
	{
		parent::beforeCompile();
		$builder = $this->getContainerBuilder();

		$app = $builder->getByType(AppManager::class);
		$builder->getDefinition($app)
			->addSetup(new Statement('$service->onInvalidateCache[] = function() {?->pages->cleanCache();}', ['@' . Model::class]));

		$hook = $builder->getByType(HookService::class);
		$hookService = $builder->getDefinition($hook);
		foreach ($this->findByType(HookFactory::class) as $def) {
			$hookService->addSetup('addHook', [$def]);
		}
	}

	/**
	 *
	 * @param string $type
	 * @return ServiceDefinition[]
	 */
	private function findByType($type)
	{
		$type = ltrim($type, '\\');
		return array_filter($this->getContainerBuilder()->getDefinitions(), function (ServiceDefinition $def) use ($type) {
			return is_a($def->getClass(), $type, true) || is_a($def->getImplement(), $type, true);
		});
	}
}
