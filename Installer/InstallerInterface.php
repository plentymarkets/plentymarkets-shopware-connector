<?php

namespace PlentyConnector\Installer;

use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;

interface InstallerInterface
{
    /**
     * @param InstallContext $context
     */
    public function install(InstallContext $context);

    /**
     * @param UpdateContext $context
     */
    public function update(UpdateContext $context);

    /**
     * @param UninstallContext $context
     */
    public function uninstall(UninstallContext $context);
}
