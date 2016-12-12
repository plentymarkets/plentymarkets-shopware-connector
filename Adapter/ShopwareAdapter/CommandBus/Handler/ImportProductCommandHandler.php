<?php

namespace ShopwareAdapter\CommandBus\Handler;

use PlentyConnector\Connector\CommandBus\Command\CommandInterface;
use PlentyConnector\Connector\CommandBus\Handler\CommandHandlerInterface;
use PlentyConnector\Connector\Identity\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;
use Shopware\Components\Api\Manager;
use Shopware\Components\Api\Resource\Variant;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class ImportProductCommandHandler.
 */
class ImportProductCommandHandler implements CommandHandlerInterface
{
    /**
     * @var Variant
     */
    private $resource;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * ImportLocalManufacturerCommandHandler constructor.
     *
     * @param IdentityServiceInterface $identityService
     */
    public function __construct(IdentityServiceInterface $identityService)
    {
        $this->resource = Manager::getResource('Article');
        $this->identityService = $identityService;
    }

    /**
     * @param CommandInterface $command
     *
     * @return bool
     */
    public function supports(CommandInterface $command)
    {
        return
            $command instanceof ImportProductCommand &&
            $command->getAdapterName() === ShopwareAdapter::getName();
    }

    /**
     * @param CommandInterface $command
     */
    public function handle(CommandInterface $command)
    {
        $product = $command->getProduct();

        $params = [
            'supplierId' => $this->getManufacturerFromProduct($product),
        ];
    }

    /**
     * @param Product $product
     *
     * @return string
     */
    private function getManufacturerFromProduct(Product $product)
    {
        $identity = $this->identityService->findIdentity([
            'objectIdentifier' => $product->getManufacturer()->getIdentifier(),
            'objectType' => Manufacturer::getType(),
            'adapterName' => ShopwareAdapter::getName(),
        ]);

        if (null !== $identity) {
            return $identity->getAdapterIdentifier();
        }

        // TODO: throw Manufacturer missing exception
    }
}
