<?php

namespace NAttreid\Analytics\DI;

use Nextras\Orm\Model\Model,
    NAttreid\AppManager\AppManager,
    Nette\DI\Statement;

/**
 * Rozsireni
 * 
 * @author Attreid <attreid@gmail.com>
 */
class WebManagerExtension extends \NAttreid\Crm\DI\ModuleExtension {

    protected $namespace = 'webManager';
    protected $dir = __DIR__;
    protected $package = 'NAttreid\\';

    public function loadConfiguration() {
        $builder = $this->getContainerBuilder();
        $config = $this->validateConfig($this->loadFromFile($this->dir . '/default.neon'), $this->config);

        if ($config['homepage'] === NULL) {
            throw new \Nette\InvalidStateException("WebManager: 'homepage' does not set in config.neon");
        }
        if ($config['page'] === NULL) {
            throw new \Nette\InvalidStateException("WebManager: 'page' does not set in config.neon");
        }

        $builder->addDefinition($this->prefix('pageService'))
                ->setClass(\NAttreid\WebManager\PageService::class)
                ->setArguments([$config['homepage'], $config['page']]);
    }

    public function beforeCompile() {
        parent::beforeCompile();
        $builder = $this->getContainerBuilder();

        $app = $builder->getByType(AppManager::class);
        $builder->getDefinition($app)
                ->addSetup(new Statement('$app->onInvalidateCache[] = function() {?->pages->cleanCache();}', ['@' . Model::class]));
    }

}
