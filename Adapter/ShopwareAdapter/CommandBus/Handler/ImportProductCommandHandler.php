<?php

namespace ShopwareAdapter\CommandBus\Handler;

use PlentyConnector\Connector\CommandBus\Command\ImportProductCommand;
use PlentyConnector\Connector\CommandBus\Handler\CommandHandlerInterface;
use PlentyConnector\Connector\Identity\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;
use PlentyConnector\Connector\TransferObject\Product;
use Shopware\Components\Api\Resource\Variant;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class ImportProductCommandHandler.
 */
class ImportProductCommandHandler implements CommandHandlerInterface
{
    use EventGeneratorTrait;

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
     * @param ImportProductCommand $command
     *
     * @return bool
     */
    public function supports($command)
    {
        return
            $command instanceof ImportProductCommand &&
            $command->getAdapterName() === ShopwareAdapter::getName()
        ;
    }

    /**
     * @param ImportProductCommand $command
     */
    public function handle($command)
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
