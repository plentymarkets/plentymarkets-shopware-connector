<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Manufacturer;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\TransferObjectCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\ServiceBus\CommandType;
use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;
use PlentyConnector\Connector\TransferObject\Media\Media;
use Shopware\Components\Api\Exception\NotFoundException as ManufacturerNotFoundException;
use Shopware\Components\Api\Resource\Manufacturer as ManufacturerResource;
use ShopwareAdapter\DataPersister\Attribute\AttributeDataPersisterInterface;
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
     * @var AttributeDataPersisterInterface
     */
    private $attributePersister;

    /**
     * HandleManufacturerCommandHandler constructor.
     *
     * @param ManufacturerResource            $resource
     * @param IdentityServiceInterface        $identityService
     * @param AttributeDataPersisterInterface $attributePersister
     */
    public function __construct(
        ManufacturerResource $resource,
        IdentityServiceInterface $identityService,
        AttributeDataPersisterInterface $attributePersister
    ) {
        $this->resource = $resource;
        $this->identityService = $identityService;
        $this->attributePersister = $attributePersister;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof TransferObjectCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME &&
            $command->getObjectType() === Manufacturer::TYPE &&
            $command->getCommandType() === CommandType::HANDLE;
    }

    /**
     * {@inheritdoc}
     *
     * @param TransferObjectCommand $command
     */
    public function handle(CommandInterface $command)
    {
        /**
         * @var Manufacturer $manufacturer
         */
        $manufacturer = $command->getPayload();

        $params = [
            'name' => $manufacturer->getName(),
        ];

        if (null !== $manufacturer->getLink()) {
            $params['link'] = $manufacturer->getLink();
        }

        if (null !== $manufacturer->getLogoIdentifier()) {
            $mediaIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => (string) $manufacturer->getLogoIdentifier(),
                'objectType' => Media::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            if (null !== $mediaIdentity) {
                $params['image'] = [
                    'mediaId' => $mediaIdentity->getAdapterIdentifier(),
                ];
            }
        }

        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => (string) $manufacturer->getIdentifier(),
            'objectType' => Manufacturer::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
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

        if ($identity) {
            try {
                $this->resource->getOne($identity->getAdapterIdentifier());
            } catch (ManufacturerNotFoundException $exception) {
                $this->identityService->remove($identity);

                $identity = null;
            }
        }

        if (null === $identity) {
            $manufacturerModel = $this->resource->create($params);

            $this->identityService->create(
                (string) $manufacturer->getIdentifier(),
                Manufacturer::TYPE,
                (string) $manufacturerModel->getId(),
                ShopwareAdapter::NAME
            );
        } else {
            $manufacturerModel = $this->resource->update($identity->getAdapterIdentifier(), $params);
        }

        $this->attributePersister->saveManufacturerAttributes(
            $manufacturerModel,
            $manufacturer->getAttributes()
        );

        return true;
    }

    /**
     * @param Manufacturer $manufacturer
     *
     * @return null|array
     */
    private function findExistingManufacturer(Manufacturer $manufacturer)
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
