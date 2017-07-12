# Web Manager pro CMS

## Nastavení
Přidat extension do **config.neon**
```neon
extensions:
    webManager: NAttreid\WebManager\DI\WebManagerExtension

webManager:
    homepage: 'Homepage:default'
    page: 'Homepage:page'
    module: 'Front'
```

## Pages
Nastavte **HomepagePresenter**
```php
class HomepagePresenter extendes Presenter {
    public $locale;

    /** @var \NAttreid\WebManager\Services\PageService @inject */
    public $pageService;

    public function actionPage($url) {
        $page = $this->pageService->getPage($url, $this->locale);

        // stranku date do template pro zobrazeni
        $this->template->page = $page;
}
```

a upravte router
```php
class FrontRouter extends Router {

    /** @var \NAttreid\WebManager\Services\PageService */
    private $pageService;

    public function __construct($url, \NAttreid\WebManager\Services\PageService $pageService) {
        parent::__construct($url);
        $this->pageService = $pageService;
    }

    public function createRoutes() {
        $routes = $this->getRouter('Front');

        $this->pageService->createRoute($routes, $this->getUrl());
        
        // nebo pokud je treba vlozit route mezi routy stranky a defaultni strankou
        
        $this->pageService->createPageRoute($routes, $this->getUrl());
        
        $routes[] = new Route(...);
        
        $this->pageService->createDefaultPageRoutes($routes, $this->getUrl());
    }

}
```

## Content
```php
class HomepagePresenter extendes Presenter {
    public $locale;

    /** @var \NAttreid\WebManager\Service @inject */
    public $webManager;

    public function actionPage($url) {
        $content = $this->webManager->getContent('main', $this->locale);

        // stranku date do template pro zobrazeni
        $this->template->content = $content;
}
```

## Hooks
```php
class SomeHook extends \NAttreid\WebManager\Services\Hooks\HookFactory
{
	/** @var IConfigurator */
	protected $configurator;

	public function create(): Component
	{
		$form = $this->formFactory->create();
		$form->setAjaxRequest();

		$form->addText('id', 'webManager.web.hooks.some.clientId')
			->setDefaultValue($this->configurator->someId);

		$form->addSubmit('save', 'form.save');

		$form->onSuccess[] = [$this, 'someFormSucceeded'];

		return $form;
	}

	public function someFormSucceeded(Form $form, $values)
	{
		$this->configurator->someId = $values->id;

		$this->flashNotifier->success('default.dataSaved');
	}
	
	// nebo DataGrid
	
	public function create(): Component
    	{
    		$form = $this->formFactory->create();
    		$form->setAjaxRequest();
    
    		$form->addText('id', 'webManager.web.hooks.some.clientId')
    			->setDefaultValue($this->configurator->someId);
    
    		$form->addSubmit('save', 'form.save');
    
    		$form->onSuccess[] = [$this, 'someFormSucceeded'];
    
    		return $form;
    	}
    
    	public function someFormSucceeded(Form $form, $values)
    	{
    		$this->configurator->someId = $values->id;
    
    		$this->flashNotifier->success('default.dataSaved');
    	}
}
```
A třídu zaregistrovat jako službu a načte se automaticky do CMS