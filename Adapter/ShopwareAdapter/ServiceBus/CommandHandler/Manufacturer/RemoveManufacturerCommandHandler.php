<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Manufacturer;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\Manufacturer\RemoveManufacturerCommand;
use PlentyConnector\Connector\ServiceBus\Command\RemoveCommandInterface;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;
use Psr\Log\LoggerInterface;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Resource\Manufacturer as ManufacturerResource;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class RemoveManufacturerCommandHandler.
 */
class RemoveManufacturerCommandHandler implements CommandHandlerInterface
{
    /**
     * @var ManufacturerResource
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
     * RemoveManufacturerCommandHandler constructor.
     *
     * @param ManufacturerResource $resource
     * @param IdentityServiceInterface $identityService
     * @param LoggerInterface $logger
     */
    public function __construct(
        ManufacturerResource $resource,
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
        return $command instanceof RemoveManufacturerCommand &&
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
            'objectType' => Manufacturer::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $identity) {
            $this->logger->notice('no matching identity found', ['command' => $command]);

            return false;
        }

        try {
            $this->resource->delete($identity->getAdapterIdentifier());
        } catch (NotFoundException $exception) {
            $this->logger->notice('identity removed but the object was not found');
        }

        $this->identityService->remove($identity);

        return true;
    }
}
