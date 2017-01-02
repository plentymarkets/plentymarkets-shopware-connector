<?php

namespace ShopwareAdapter\CommandBus\CommandHandler\Manufacturer;

use PlentyConnector\Connector\CommandBus\Command\CommandInterface;
use PlentyConnector\Connector\CommandBus\Command\Manufacturer\RemoveManufacturerCommand;
use PlentyConnector\Connector\CommandBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\EventBus\EventGeneratorTrait;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;
use Shopware\Components\Api\Resource\Manufacturer as ManufacturerResource;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class RemoveManufacturerCommandHandler.
 */
class RemoveManufacturerCommandHandler implements CommandHandlerInterface
{
    use EventGeneratorTrait;

    /**
     * @var ManufacturerResource
     */
    private $resource;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * RemoveManufacturerCommandHandler constructor.
     *
     * @param ManufacturerResource $resource
     * @param IdentityServiceInterface $identityService
     */
    public function __construct(ManufacturerResource $resource, IdentityServiceInterface $identityService)
    {
        $this->resource = $resource;
        $this->identityService = $identityService;
    }

    /**
     * @param CommandInterface $command
     *
     * @return boolean
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof RemoveManufacturerCommand &&
            $command->getAdapterName() === ShopwareAdapter::getName();
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
         * @var RemoveManufacturerCommand $command
         */
        $manufacturer = $command->getManufacturer();

        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $manufacturer->getIdentifier(),
            'objectType' => Manufacturer::getType(),
            'adapterName' => ShopwareAdapter::getName(),
        ]);

        if (null === $identity) {
            return;
        }

        $this->resource->delete($identity->getAdapterIdentifier());
        $this->identityService->remove($identity);
    }
}
