# Web Manager pro CRM

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

nastavit **HomepagePresenter**
```php
class HomepagePresenter extendes Presenter {
    public $locale;

    /** @var \NAttreid\WebManager\PageService @inject */
    public $pageService;

    public function actionPage($url) {
        $page = $this->pageService->getPage($url, $this->locale);

        // stranku date do template pro zobrazeni
        $this->template->page = $page;
}
```

a upravit router
```php
class FrontRouter extends Router {

    /** @var \NAttreid\WebManager\PageService */
    private $pageService;

    public function __construct($url, \NAttreid\WebManager\PageService $pageService) {
        parent::__construct($url);
        $this->pageService = $pageService;
    }

    public function createRoutes() {
        $routes = $this->getRouter('Front');

        $this->pageService->createRoute($routes, $this->getUrl());
    }

}
