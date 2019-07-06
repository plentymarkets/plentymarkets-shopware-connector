<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Manufacturer;

use Psr\Log\LoggerInterface;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Resource\Manufacturer as ManufacturerResource;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\IdentityService\Struct\Identity;
use SystemConnector\ServiceBus\Command\CommandInterface;
use SystemConnector\ServiceBus\Command\TransferObjectCommand;
use SystemConnector\ServiceBus\CommandHandler\CommandHandlerInterface;
use SystemConnector\ServiceBus\CommandType;
use SystemConnector\TransferObject\Manufacturer\Manufacturer;

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
    public function supports(CommandInterface $command): bool
    {
        return $command instanceof TransferObjectCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME &&
            $command->getObjectType() === Manufacturer::TYPE &&
            $command->getCommandType() === CommandType::REMOVE;
    }

    /**
     * {@inheritdoc}
     *
     * @param TransferObjectCommand $command
     *
     * @throws ParameterMissingException
     */
    public function handle(CommandInterface $command): bool
    {
        $identifier = $command->getPayload();

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
