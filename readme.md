# Web Manager pro CRM

## Nastavení
Přidat extension do **config.neon**
```neon
extensions:
    webManager: NAttreid\WebManager\DI\WebManagerExtension

webManager:
    homepage: 'Homepage:default'
    page: 'Homepage:page'
```

nastavit **HomepagePresenter**
```php
class HomepagePresenter extendes Presenter {
    /** @var \NAttreid\WebManager\PageService @inject */
    public $pageService;

    public function actionPage($url) {
        $page = $this->pageService->getPage($url);

        // stranku dat template pro zobrazeni
        $this->template->page = $page;
}
```

a upravit router
```php
class FrontRouter extends Router {

    /** @var \NAttreid\WebManager\PageService */
    private $pageService;

    public function __construct($url, $secured, \NAttreid\WebManager\PageService $pageService) {
        parent::__construct($url, $secured);
        $this->pageService = $pageService;
    }

    public function createRoutes() {
        $routes = $this->getRouter('Front');

        $routes[] = new Route($this->getUrl() . 'sitemap.xml', 'Feed:sitemap');

        $this->pageService->createRoute($routes, $this->getUrl(), $this->getFlag());
    }

}
