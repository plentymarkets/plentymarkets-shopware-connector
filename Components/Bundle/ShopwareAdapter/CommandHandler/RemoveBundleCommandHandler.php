<?php

namespace PlentyConnector\Components\Bundle\ShopwareAdapter\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PlentyConnector\Components\Bundle\Helper\BundleHelper;
use PlentyConnector\Components\Bundle\TransferObject\Bundle;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\TransferObjectCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\ServiceBus\CommandType;
use PlentyConnector\Connector\ValueObject\Identity\Identity;
use Psr\Log\LoggerInterface;
use ShopwareAdapter\ShopwareAdapter;
use SwagBundle\Models\Bundle as BundleModel;

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
     * @var LoggerInterface
     */
    private $logger;


    public function __construct(
        IdentityServiceInterface $identityService,
        EntityManagerInterface $entityManager,
        BundleHelper $bundleHelper,
        LoggerInterface $logger
    ) {
        $this->identityService = $identityService;
        $this->entityManager = $entityManager;
        $this->bundleHelper = $bundleHelper;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof TransferObjectCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME &&
            $command->getObjectType() === Bundle::TYPE &&
            $command->getCommandType() === CommandType::REMOVE;
    }

    /**
     * {@inheritdoc}
     *
     * @param TransferObjectCommand $command
     */
    public function handle(CommandInterface $command)
    {
        $identifier = $command->getPayload();

        $this->bundleHelper->registerBundleModels();

        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => (string) $identifier,
            'objectType' => Bundle::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $identity) {
            $this->logger->notice('no matching identity found', ['command' => $command]);

            return false;
        }

        /**
         * @var EntityRepository $repository
         */
        $repository = $this->entityManager->getRepository(BundleModel::class);

        /**
         * @var BundleModel $bundleModel
         */
        $bundleModel = $repository->find($identity->getAdapterIdentifier());

        if (null === $bundleModel) {
            $this->logger->notice('identity removed but the object was not found', ['command' => $command]);

            return false;
        }

        $this->entityManager->remove($bundleModel);
        $this->entityManager->flush();

        $identities = $this->identityService->findBy([
            'objectIdentifier' => $identifier,
        ]);

        array_walk($identities, function (Identity $identity) {
            $this->identityService->remove($identity);
        });

        return true;
    }
}
