<?php

namespace NAttreid\WebManager;

/**
 * Interface IConfigurator
 *
 * @property boolean $cookiePolicy potvrzeni pouzivani cookie
 * @property string $cookiePolicyLink link pro informace o pouzivani cookie
 * @property string $keywords klicova slova
 * @property string $description popis
 * @property string $logo logo
 * @property string $googleAnalyticsClientId Id google analytics
 * @property string $webmasterHash hash pro webmaster tools
 *
 * @author Attreid <attreid@gmail.com>
 */
interface IConfigurator extends \NAttreid\Crm\Configurator\IConfigurator
{

}
