<?php

namespace PlentyConnector\Installer;

use Exception;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Shopware_Components_Acl;

/**
 * Class PermissionInstaller
 */
class PermissionInstaller implements InstallerInterface
{
    const PERMISSION_READ = 'read';
    const PERMISSION_WRITE = 'write';

    /**
     * List of all permissions
     */
    const PERMISSIONS = [
        self::PERMISSION_READ,
        self::PERMISSION_WRITE,
    ];

    /**
     * @var Shopware_Components_Acl
     */
    private $acl;

    /**
     * PermissionInstaller constructor.
     *
     * @param Shopware_Components_Acl $acl
     */
    public function __construct(Shopware_Components_Acl $acl)
    {
        $this->acl = $acl;
    }

    /**
     * @param InstallContext $context
     */
    private function removePermissions(InstallContext $context)
    {
        $this->acl->deleteResource($context->getPlugin()->getName());
    }

    /**
     * @param InstallContext $context
     *
     * @throws Exception
     */
    private function createPermissions(InstallContext $context)
    {
        $this->acl->createResource(
            $context->getPlugin()->getName(),
            self::PERMISSIONS,
            $context->getPlugin()->getLabel(),
            $context->getPlugin()->getId()
        );
    }

    /**
     * @param InstallContext $context
     *
     * @throws Exception
     */
    public function install(InstallContext $context)
    {
        $this->removePermissions($context);
        $this->createPermissions($context);
    }

    /**
     * @param UpdateContext $context
     *
     * @throws Exception
     */
    public function update(UpdateContext $context)
    {
        $this->removePermissions($context);
        $this->createPermissions($context);
    }

    /**
     * @param UninstallContext $context
     *
     * @throws Exception
     */
    public function uninstall(UninstallContext $context)
    {
        $this->removePermissions($context);
    }
}
