<?php

namespace ShopwareAdapter\CommandBus\CommandHandler\Manufacturer;

use PlentyConnector\Connector\CommandBus\Command\CommandInterface;
use PlentyConnector\Connector\CommandBus\Command\Manufacturer\HandleManufacturerCommand;
use PlentyConnector\Connector\CommandBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\EventBus\EventGeneratorTrait;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;
use PlentyConnector\Connector\TransferObject\Manufacturer\ManufacturerInterface;
use Shopware\Components\Api\Resource\Manufacturer as ManufacturerResource;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class HandleManufacturerCommandHandler.
 */
class HandleManufacturerCommandHandler implements CommandHandlerInterface
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
     * HandleManufacturerCommandHandler constructor.
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
     * @return bool
     */
    public function supports(CommandInterface $command)
    {
        return
            $command instanceof HandleManufacturerCommand &&
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
         * @var HandleManufacturerCommand $command
         * @var ManufacturerInterface $manufacturer
         */
        $manufacturer = $command->getTransferObject();

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

        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $manufacturer->getIdentifier(),
            'objectType' => Manufacturer::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        $createManufacturer = true;

        if (null === $identity) {
            $existingManufacturer = $this->findExistingManufacturer($manufacturer);

            if (null !== $existingManufacturer) {
                $identity = $this->identityService->create(
                    $manufacturer->getIdentifier(),
                    Manufacturer::TYPE,
                    (string)$existingManufacturer['id'],
                    ShopwareAdapter::NAME
                );

                $createManufacturer = false;
            }
        } else {
            $createManufacturer = false;
        }

        if ($createManufacturer) {
            $manufacturerModel = $this->resource->create($params);

            $this->identityService->create(
                $manufacturer->getIdentifier(),
                Manufacturer::TYPE,
                (string)$manufacturerModel->getId(),
                ShopwareAdapter::NAME
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
            return null;
        }

        return array_shift($result['data']);
    }
}
