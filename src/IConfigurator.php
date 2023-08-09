<?php

declare(strict_types=1);

namespace NAttreid\WebManager;

/**
 * Interface IConfigurator
 *
 * @property string $keywords klicova slova
 * @property string $title nazev
 * @property string $description popis
 * @property string $logo logo
 * @property string $headerTags header tagy
 * @property string $tags paticka tagy
 * @property bool $onePage vsechny stranky do jedne
 *
 * @author Attreid <attreid@gmail.com>
 */
interface IConfigurator extends \NAttreid\Cms\Configurator\IConfigurator
{

}
