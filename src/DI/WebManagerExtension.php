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

        $builder->addDefinition($this->prefix('routeFactory'))
                ->setClass(\NAttreid\WebManager\Routing\RouteFactory::class);
    }

    public function beforeCompile() {
        parent::beforeCompile();
        $builder = $this->getContainerBuilder();

        $app = $builder->getByType(AppManager::class);
        $builder->getDefinition($app)
                ->addSetup(new Statement('$app->onInvalidateCache[] = function() {?->pages->cleanCache();}', ['@' . Model::class]));

        $builder->getDefinition('application.presenterFactory')
                ->addSetup('setMapping', [
                    ['WebManagerExt' => 'NAttreid\WebManager\Front\*Presenter']
        ]);
    }

}
