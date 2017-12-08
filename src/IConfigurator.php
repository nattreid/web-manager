<?php

declare(strict_types=1);

namespace NAttreid\WebManager;

/**
 * Interface IConfigurator
 *
 * @property bool $cookiePolicy potvrzeni pouzivani cookie
 * @property string $cookiePolicyLink link pro informace o pouzivani cookie
 * @property string $keywords klicova slova
 * @property string $title nazev
 * @property string $description popis
 * @property string $logo logo
 * @property string $tags tagy
 *
 * @author Attreid <attreid@gmail.com>
 */
interface IConfigurator extends \NAttreid\Cms\Configurator\IConfigurator
{

}
