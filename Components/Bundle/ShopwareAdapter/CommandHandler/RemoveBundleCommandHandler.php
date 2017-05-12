<?php

namespace PlentyConnector\Components\Bundle\ShopwareAdapter\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Components\Bundle\Command\RemoveBundleCommand;
use PlentyConnector\Components\Bundle\Helper\BundleHelper;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class RemoveBundleCommandHandler.
 */
class RemoveBundleCommandHandler implements CommandHandlerInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var BundleHelper
     */
    private $bundleHelper;

    /**
     * RemoveBundleCommandHandler constructor.
     *
     * @param IdentityServiceInterface $identityService
     * @param EntityManagerInterface $entityManager
     * @param BundleHelper $bundleHelper
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        EntityManagerInterface $entityManager,
        BundleHelper $bundleHelper
    ) {
        $this->identityService = $identityService;
        $this->entityManager = $entityManager;
        $this->bundleHelper = $bundleHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof RemoveBundleCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function handle(CommandInterface $command)
    {
        /**
         * @var RemoveBundleCommand $command
         */
        $identifier = $command->getObjectIdentifier();

        $this->bundleHelper->registerBundleModels();

        return true;
    }
}
