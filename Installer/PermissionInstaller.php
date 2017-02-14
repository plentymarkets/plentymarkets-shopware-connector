<?php

namespace PlentyConnector\Installer;

use Exception;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\User\Resource;
use Shopware_Components_Acl;

/**
 * Class PermissionInstaller
 */
class PermissionInstaller implements InstallerInterface
{
    /**
     * @var ModelManager
     */
    private $em;

    /**
     * @var ModelRepository
     */
    private $repository;

    /**
     * @var Shopware_Components_Acl
     */
    private $acl;

    /**
     * @var array
     */
    private $permissions;

    /**
     * CronjobSyncronizer constructor.
     *
     * @param ModelManager $em
     * @param Shopware_Components_Acl $acl
     * @param array $permissions
     */
    public function __construct(ModelManager $em, Shopware_Components_Acl $acl, array $permissions)
    {
        $this->em = $em;
        $this->repository = $this->em->getRepository(Resource::class);
        $this->acl = $acl;
        $this->permissions = $permissions;
    }

    /**
     * @param InstallContext $context
     *
     * @throws Exception
     */
    public function install(InstallContext $context)
    {
        $this->synchronize($context->getPlugin(), $this->permissions);
    }

    /**
     * @param UpdateContext $context
     *
     * @throws Exception
     */
    public function update(UpdateContext $context)
    {
        $this->synchronize($context->getPlugin(), $this->permissions);
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

    /**
     * @param Resource $resource
     * @param array $permissions
     */
    protected function removeNotExistingPrivileges(Resource $resource, array $permissions)
    {
        $existingPrivileges = $resource->getPrivileges()->toArray();

        $orphanedPrivileges = array_filter($existingPrivileges, function (Privilege $privilege) use ($permissions) {
            return !in_array($privilege->getName(), $permissions, true);
        });

        if (empty($orphanedPrivileges)) {
            return;
        }

        array_walk($orphanedPrivileges, function (Privilege $privilege) {
            $this->em->remove($privilege);
        });

        $this->em->flush();
    }

    /**
     * @param Plugin $plugin
     * @param array $permissions
     */
    private function synchronize(Plugin $plugin, array $permissions)
    {
        $resource = $this->getResource($plugin->getName());

        if (null === $resource) {
            $this->createResource($plugin, $permissions);

            return;
        }

        $this->synchronizePrivileges($resource, $permissions);
        $this->removeNotExistingPrivileges($resource, $permissions);
    }

    /**
     * @param $resourceName
     *
     * @return Resource
     */
    private function getResource($resourceName)
    {
        return $this->repository->findOneBy(['name' => $resourceName]);
    }

    /**
     * @param Plugin $plugin
     * @param array $permissions
     *
     * @throws Enlight_Exception
     */
    private function createResource(Plugin $plugin, array $permissions)
    {
        $this->acl->createResource(
            $plugin->getName(),
            $permissions,
            $plugin->getLabel(),
            $plugin->getId()
        );
    }

    /**
     * @param Resource $resource
     * @param array $permissions
     */
    private function synchronizePrivileges(Resource $resource, array $permissions)
    {
        $existingPrivileges = array_filter($resource->getPrivileges()->toArray(), function (Privilege $privilege) use ($permissions) {
            return in_array($privilege->getName(), $permissions, true);
        });

        $existingPrivileges = array_map(function (Privilege $privilege) {
            return $privilege->getName();
        }, $existingPrivileges);

        $newPrivileges = array_diff($permissions, $existingPrivileges);

        array_walk($newPrivileges, function ($name) use ($resource) {
            $this->acl->createPrivilege($resource->getId(), $name);
        });
    }

    /**
     * @param InstallContext $context
     */
    private function removePermissions(InstallContext $context)
    {
        $this->acl->deleteResource($context->getPlugin()->getName());
    }
}
