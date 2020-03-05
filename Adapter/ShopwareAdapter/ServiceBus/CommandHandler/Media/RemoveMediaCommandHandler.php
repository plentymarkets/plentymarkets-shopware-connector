<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Media;

use Psr\Log\LoggerInterface;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Manager;
use Shopware\Components\Api\Resource\Media as MediaResource;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\IdentityService\Struct\Identity;
use SystemConnector\ServiceBus\Command\CommandInterface;
use SystemConnector\ServiceBus\Command\TransferObjectCommand;
use SystemConnector\ServiceBus\CommandHandler\CommandHandlerInterface;
use SystemConnector\ServiceBus\CommandType;
use SystemConnector\TransferObject\Media\Media;

class RemoveMediaCommandHandler implements CommandHandlerInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        IdentityServiceInterface $identityService,
        LoggerInterface $logger
    ) {
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
            $command->getObjectType() === Media::TYPE &&
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
            'objectType' => Media::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $identity) {
            $this->logger->notice('no matching identity found', ['command' => $command]);

            return false;
        }

        try {
            $resource = $this->getMediaResource();
            $resource->delete($identity->getAdapterIdentifier());
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

    private function getMediaResource(): MediaResource
    {
        // without this reset the entitymanager sometimes the album is not found correctly.
        Shopware()->Container()->reset('models');

        return Manager::getResource('Media');
    }
}
