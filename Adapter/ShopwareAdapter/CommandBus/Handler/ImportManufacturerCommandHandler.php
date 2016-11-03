<?php

namespace ShopwareAdapter\CommandBus\Handler;

use PlentyConnector\Connector\CommandBus\Command\ImportManufacturerCommand;
use PlentyConnector\Connector\CommandBus\Handler\CommandHandlerInterface;
use PlentyConnector\Connector\EventBus\EventGeneratorTrait;
use PlentyConnector\Connector\Identity\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;
use PlentyConnector\Connector\TransferObject\Manufacturer\ManufacturerInterface;
use Shopware\Components\Api\Manager;
use Shopware\Components\Api\Resource\Manufacturer as ManufacturerResource;
use Shopware\Models\Article\Supplier;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class ImportLocalManufacturerCommandHandler.
 */
class ImportManufacturerCommandHandler implements CommandHandlerInterface
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
     * ImportLocalManufacturerCommandHandler constructor.
     *
     * @param IdentityServiceInterface $identityService
     */
    public function __construct(IdentityServiceInterface $identityService)
    {
        $this->resource = Manager::getResource('Manufacturer');
        $this->identityService = $identityService;
    }

    /**
     * @param ImportManufacturerCommand $command
     *
     * @return bool
     */
    public function supports($command)
    {
        return
            $command instanceof ImportManufacturerCommand &&
            $command->getAdapterName() === ShopwareAdapter::getName()
        ;
    }

    /**
     * @param ImportManufacturerCommand $command
     *
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function handle($command)
    {
        $manufacturer = $command->getManufacturer();

        $params = [
            'name' => $manufacturer->getName(),
        ];

        if (null !== $manufacturer->getLink()) {
            $params['link'] = $manufacturer->getLink();
        }

        if (null !== $manufacturer->getLogo()) {
            $params['image'] = [
                'link' => $manufacturer->getLogo(),
            ];
        }

        $identity = $this->identityService->findIdentity([
            'objectIdentifier' => $manufacturer->getIdentifier(),
            'objectType' => Manufacturer::getType(),
            'adapterName' => ShopwareAdapter::getName(),
        ]);

        $createManufacturer = true;

        if (null === $identity) {
            $existingManufacturer = $this->findExistingManufacturer($manufacturer);

            if (null !== $existingManufacturer) {
                $identity = $this->identityService->createIdentity(
                    $manufacturer->getIdentifier(),
                    Manufacturer::getType(),
                    (string) $existingManufacturer->getId(),
                    ShopwareAdapter::getName()
                );

                $createManufacturer = false;
            }
        } else {
            $createManufacturer = false;
        }

        if ($createManufacturer) {
            $manufacturerModel = $this->resource->create($params);

            $this->identityService->createIdentity(
                $manufacturer->getIdentifier(),
                Manufacturer::getType(),
                (string) $manufacturerModel->getId(),
                ShopwareAdapter::getName()
            );
        } else {
            $this->resource->update($identity->getAdapterIdentifier(), $params);
        }
    }

    /**
     * @param ManufacturerInterface $manufacturer
     *
     * @return Supplier|null
     */
    private function findExistingManufacturer(ManufacturerInterface $manufacturer)
    {
        $result = $this->resource->getList(0, 1, [
            'supplier.name' => $manufacturer->getName(),
        ]);

        if (0 === count($result['data'])) {
            return;
        }

        return array_shift($result['data']);
    }
}
