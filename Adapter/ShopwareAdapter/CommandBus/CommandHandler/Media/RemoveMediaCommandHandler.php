<?php

namespace ShopwareAdapter\CommandBus\CommandHandler\Media;

use PlentyConnector\Connector\CommandBus\Command\CommandInterface;
use PlentyConnector\Connector\CommandBus\Command\Media\RemoveMediaCommand;
use PlentyConnector\Connector\CommandBus\Command\RemoveCommandInterfaca;
use PlentyConnector\Connector\CommandBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Media\Media;
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
     * RemoveMediaCommandHandler constructor.
     *
     * @param MediaResource $resource
     * @param IdentityServiceInterface $identityService
     */
    public function __construct(MediaResource $resource, IdentityServiceInterface $identityService)
    {
        $this->resource = $resource;
        $this->identityService = $identityService;
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
     * @param CommandInterface $command
     *
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function handle(CommandInterface $command)
    {
        /**
         * @var RemoveCommandInterfaca $command
         */
        $identifier = $command->getObjectIdentifier();

        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $identifier,
            'objectType' => Media::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $identity) {
            return;
        }

        $this->resource->delete($identity->getAdapterIdentifier());
        $this->identityService->remove($identity);
    }
}
