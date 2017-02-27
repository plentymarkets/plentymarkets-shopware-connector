<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Media;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\Media\RemoveMediaCommand;
use PlentyConnector\Connector\ServiceBus\Command\RemoveCommandInterface;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\TransferObject\Media\Media;
use PlentyConnector\Connector\ValueObject\Identity\Identity;
use Psr\Log\LoggerInterface;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Resource\Media as MediaResource;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class RemoveMediaCommandHandler.
 */
class RemoveMediaCommandHandler implements CommandHandlerInterface
{
    /**
     * @var MediaResource
     */
    private $resource;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * RemoveMediaCommandHandler constructor.
     *
     * @param MediaResource $resource
     * @param IdentityServiceInterface $identityService
     * @param LoggerInterface $logger
     */
    public function __construct(
        MediaResource $resource,
        IdentityServiceInterface $identityService,
        LoggerInterface $logger
    ) {
        $this->resource = $resource;
        $this->identityService = $identityService;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof RemoveMediaCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(CommandInterface $command)
    {
        /**
         * @var RemoveCommandInterface $command
         */
        $identifier = $command->getObjectIdentifier();

        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => (string) $identifier,
            'objectType' => Media::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $identity) {
            $this->logger->notice('no matching identity found', ['command' => $command]);

            return false;
        }

        try {
            $this->resource->delete($identity->getAdapterIdentifier());
        } catch (NotFoundException $exception) {
            $this->logger->notice('identity removed but the object was not found', ['command' => $command]);
        }

        $identities = $this->identityService->findBy([
            'objectIdentifier' => $identifier,
        ]);

        array_walk($identities, function(Identity $identity) {
            $this->identityService->remove($identity);
        });

        return true;
    }
}
