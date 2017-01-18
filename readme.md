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

    /** @var \NAttreid\WebManager\Service @inject */
    public $webManager;

    public function actionPage($url) {
        $page = $this->webManager->getPage($url, $this->locale);

        // stranku date do template pro zobrazeni
        $this->template->page = $page;
}
```

a upravte router
```php
class FrontRouter extends Router {

    /** @var \NAttreid\WebManager\Service */
    private $webManager;

    public function __construct($url, \NAttreid\WebManager\Service $webManager) {
        parent::__construct($url);
        $this->webManager = $webManager;
    }

    public function createRoutes() {
        $routes = $this->getRouter('Front');

        $this->$webManager->createRoute($routes, $this->getUrl());
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