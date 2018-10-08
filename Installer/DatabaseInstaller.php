<?php

namespace PlentyConnector\Installer;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\SchemaTool;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;

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

    public function __construct(ModelManager $entityManager, array $models)
    {
        $this->schemaTool = new SchemaTool($entityManager);

        foreach ($models as $model) {
            $this->models[] = $entityManager->getClassMetadata($model);
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
