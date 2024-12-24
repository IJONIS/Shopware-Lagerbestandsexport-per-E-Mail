<?php declare(strict_types=1);

namespace Lagerbestandsexport;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;

class Lagerbestandsexport extends Plugin
{
    public function install(InstallContext $installContext): void
    {
        parent::install($installContext);
    }
}
