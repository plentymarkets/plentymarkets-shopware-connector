<?php

namespace PlentyConnector\Installer;

use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;

interface InstallerInterface
{
    public function install(InstallContext $context);

    public function update(UpdateContext $context);

    public function uninstall(UninstallContext $context);
}
