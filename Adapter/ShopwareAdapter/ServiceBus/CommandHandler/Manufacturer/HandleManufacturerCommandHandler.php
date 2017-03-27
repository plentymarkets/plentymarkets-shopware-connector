<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Manufacturer;

use PlentyConnector\Connector\IdentityService\Exception\NotFoundException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\HandleCommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\Manufacturer\HandleManufacturerCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;
use PlentyConnector\Connector\TransferObject\Media\Media;
use Shopware\Components\Api\Resource\Manufacturer as ManufacturerResource;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class HandleManufacturerCommandHandler.
 */
class HandleManufacturerCommandHandler implements CommandHandlerInterface
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
     * HandleManufacturerCommandHandler constructor.
     *
     * @param ManufacturerResource     $resource
     * @param IdentityServiceInterface $identityService
     */
    public function __construct(ManufacturerResource $resource, IdentityServiceInterface $identityService)
    {
        $this->resource = $resource;
        $this->identityService = $identityService;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof HandleManufacturerCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(CommandInterface $command)
    {
        /**
         * @var HandleCommandInterface
         * @var Manufacturer           $manufacturer
         */
        $manufacturer = $command->getTransferObject();

        $params = [
            'name' => $manufacturer->getName(),
        ];

        if (null !== $manufacturer->getLink()) {
            $params['link'] = $manufacturer->getLink();
        }

        if (null !== $manufacturer->getLogoIdentifier()) {
            $mediaIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => (string) $manufacturer->getLogoIdentifier(),
                'objectType'       => Media::TYPE,
                'adapterName'      => ShopwareAdapter::NAME,
            ]);

            if (null === $mediaIdentity) {
                throw new NotFoundException('Missing Media for Adapter');
            }

            $params['image'] = [
                'mediaId' => $mediaIdentity->getAdapterIdentifier(),
            ];
        }

        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => (string) $manufacturer->getIdentifier(),
            'objectType'       => Manufacturer::TYPE,
            'adapterName'      => ShopwareAdapter::NAME,
        ]);

        if (null === $identity) {
            $existingManufacturer = $this->findExistingManufacturer($manufacturer);

            if (null !== $existingManufacturer) {
                $identity = $this->identityService->create(
                    (string) $manufacturer->getIdentifier(),
                    Manufacturer::TYPE,
                    (string) $existingManufacturer['id'],
                    ShopwareAdapter::NAME
                );
            }
        }

        if (null === $identity) {
            $newManufacturer = $this->resource->create($params);

            $this->identityService->create(
                (string) $manufacturer->getIdentifier(),
                Manufacturer::TYPE,
                (string) $newManufacturer->getId(),
                ShopwareAdapter::NAME
            );
        } else {
            $this->resource->update($identity->getAdapterIdentifier(), $params);
        }

        return true;
    }

    /**
     * @param Manufacturer $manufacturer
     *
     * @return null|Supplier
     */
    private function findExistingManufacturer(Manufacturer $manufacturer)
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
