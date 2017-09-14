<?php

declare(strict_types=1);

namespace NAttreid\WebManager\DI;

use NAttreid\AppManager\AppManager;
use NAttreid\Cms\DI\ModuleExtension;
use NAttreid\Gallery\DI\GalleryExtension;
use NAttreid\WebManager\Components\ILinksFactory;
use NAttreid\WebManager\Components\Links;
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

	public function loadConfiguration(): void
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
			->setType(PageService::class)
			->setArguments([$config['homepage'], $config['page'], $config['module']]);

		$builder->addDefinition($this->prefix('links'))
			->setImplement(ILinksFactory::class)
			->setFactory(Links::class);

		$builder->addDefinition($this->prefix('hookService'))
			->setType(HookService::class);

		$gallery = new GalleryExtension();
		$gallery->setCompiler($this->compiler, 'webManagerGallery');
		$gallery->loadConfiguration();
	}

	public function beforeCompile(): void
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

		$gallery = new GalleryExtension();
		$gallery->setCompiler($this->compiler, 'gallery');
		$gallery->beforeCompile();
	}

	/**
	 *
	 * @param string $type
	 * @return ServiceDefinition[]
	 */
	private function findByType(string $type): array
	{
		$type = ltrim($type, '\\');
		return array_filter($this->getContainerBuilder()->getDefinitions(), function (ServiceDefinition $def) use ($type) {
			return is_a($def->getType(), $type, true) || is_a($def->getImplement(), $type, true);
		});
	}
}
