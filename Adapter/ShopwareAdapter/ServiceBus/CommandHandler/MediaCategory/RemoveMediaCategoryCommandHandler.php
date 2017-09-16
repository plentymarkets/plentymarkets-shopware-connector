<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\MediaCategory;

use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\TransferObjectCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\ServiceBus\CommandType;
use PlentyConnector\Connector\TransferObject\Media\Media;
use PlentyConnector\Connector\TransferObject\MediaCategory\MediaCategory;
use PlentyConnector\Connector\ValueObject\Identity\Identity;
use Psr\Log\LoggerInterface;
use Shopware\Models\Media\Album;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class RemoveMediaCategoryCommandHandler.
 */
class RemoveMediaCategoryCommandHandler implements CommandHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * HandleMediaCategoryCommandHandler constructor.
     *
     * @param EntityManagerInterface   $entityManager
     * @param IdentityServiceInterface $identityService
     * @param LoggerInterface          $logger
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        IdentityServiceInterface $identityService,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->identityService = $identityService;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof TransferObjectCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME &&
            $command->getObjectType() === MediaCategory::TYPE &&
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

        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => (string) $identifier,
            'objectType' => Media::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $identity) {
            $this->logger->notice('no matching identity found', ['command' => $command]);

            return false;
        }

        $repository = $this->entityManager->getRepository(Album::class);

        $album = $repository->find($identity->getAdapterIdentifier());

        if (null !== $album) {
            $this->entityManager->remove($album);
            $this->entityManager->flush();
        } else {
            $this->logger->notice('identity removed but the object was not found', ['command' => $command]);
        }

        $identities = $this->identityService->findBy([
            'objectIdentifier' => $identifier,
        ]);

        array_walk($identities, function (Identity $identity) {
            $this->identityService->remove($identity);
        });

        return true;
    }
}
