<?php

namespace PlentyConnector\Installer;

use Doctrine\ORM\EntityRepository;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\User\Privilege as ShopwarePrivilege;
use Shopware\Models\User\Resource as ShopwareResource;
use Shopware_Components_Acl;

class PermissionInstaller implements InstallerInterface
{
    /**
     * @var ModelManager
     */
    private $em;

    /**
     * @var EntityRepository
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

    public function __construct(ModelManager $em, Shopware_Components_Acl $acl, array $permissions)
    {
        $this->em = $em;
        $this->repository = $this->em->getRepository(ShopwareResource::class);
        $this->acl = $acl;
        $this->permissions = $permissions;
    }

    public function install(InstallContext $context)
    {
        $this->synchronize($context->getPlugin(), $this->permissions);
    }

    public function update(UpdateContext $context)
    {
        $this->synchronize($context->getPlugin(), $this->permissions);
    }

    public function uninstall(UninstallContext $context)
    {
        $this->removePermissions($context);
    }

    protected function removeNotExistingPrivileges(ShopwareResource $resource, array $permissions)
    {
        $existingPrivileges = $resource->getPrivileges()->toArray();

        $orphanedPrivileges = array_filter($existingPrivileges, static function (ShopwarePrivilege $privilege) use ($permissions) {
            return !in_array($privilege->getName(), $permissions, true);
        });

        if (empty($orphanedPrivileges)) {
            return;
        }

        array_walk($orphanedPrivileges, function (ShopwarePrivilege $privilege) {
            $this->em->remove($privilege);
        });

        $this->em->flush();
    }

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
     * @param string $resourceName
     *
     * @return null|ShopwareResource
     */
    private function getResource($resourceName)
    {
        return $this->repository->findOneBy(['name' => $resourceName]);
    }

    private function createResource(Plugin $plugin, array $permissions)
    {
        $this->acl->createResource(
            $plugin->getName(),
            $permissions,
            $plugin->getLabel(),
            $plugin->getId()
        );
    }

    private function synchronizePrivileges(ShopwareResource $resource, array $permissions)
    {
        $existingPrivileges = array_filter($resource->getPrivileges()->toArray(), static function (ShopwarePrivilege $privilege) use ($permissions) {
            return in_array($privilege->getName(), $permissions, true);
        });

        $existingPrivileges = array_map(
            static function (ShopwarePrivilege $privilege) {
                return $privilege->getName();
            }, $existingPrivileges);

        $newPrivileges = array_diff($permissions, $existingPrivileges);

        array_walk($newPrivileges, function ($name) use ($resource) {
            $this->acl->createPrivilege($resource->getId(), $name);
        });
    }

    private function removePermissions(InstallContext $context)
    {
        $this->acl->deleteResource($context->getPlugin()->getName());
    }
}
