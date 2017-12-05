<?php

namespace PlentyConnector\Installer;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;

/**
 * Class Database.
 */
class DatabaseInstaller implements InstallerInterface
{
    /**
     * @var SchemaTool
     */
    private $schemaTool;

    /**
     * @var ClassMetadata[]
     */
    private $models = [];

    /**
     * DatabaseInstaller constructor.
     *
     * @param EntityManagerInterface $entitiyManager
     * @param array                  $models
     */
    public function __construct(EntityManagerInterface $entitiyManager, array $models)
    {
        $this->schemaTool = new SchemaTool($entitiyManager);

        foreach ($models as $model) {
            $this->models[] = $entitiyManager->getClassMetadata($model);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function install(InstallContext $context)
    {
        $this->schemaTool->updateSchema($this->models, true);
    }

    /**
     * {@inheritdoc}
     */
    public function update(UpdateContext $context)
    {
        $this->schemaTool->updateSchema($this->models, true);
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(UninstallContext $context)
    {
        if (!$context->keepUserData()) {
            $this->schemaTool->dropSchema($this->models);
        }
    }
}
