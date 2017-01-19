<?php

namespace ShopwareAdapter\CommandBus\CommandHandler\Category;

use PlentyConnector\Connector\CommandBus\Command\Category\RemoveCategoryCommand;
use PlentyConnector\Connector\CommandBus\Command\CommandInterface;
use PlentyConnector\Connector\CommandBus\Command\RemoveCommandInterfaca;
use PlentyConnector\Connector\CommandBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Category\Category;
use Shopware\Components\Api\Resource\Category as CategoryResource;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class RemoveCategoryCommandHandler.
 */
class RemoveCategoryCommandHandler implements CommandHandlerInterface
{
    /**
     * @var CategoryResource
     */
    private $resource;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * RemoveCategoryCommandHandler constructor.
     *
     * @param CategoryResource $resource
     * @param IdentityServiceInterface $identityService
     */
    public function __construct(CategoryResource $resource, IdentityServiceInterface $identityService)
    {
        $this->resource = $resource;
        $this->identityService = $identityService;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof RemoveCategoryCommand &&
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
            'objectType' => Category::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $identity) {
            return;
        }

        $this->resource->delete($identity->getAdapterIdentifier());
        $this->identityService->remove($identity);
    }
}
