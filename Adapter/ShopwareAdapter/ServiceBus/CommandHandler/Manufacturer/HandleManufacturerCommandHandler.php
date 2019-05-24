<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Manufacturer;

use Shopware\Components\Api\Exception\NotFoundException as ManufacturerNotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Components\Api\Resource\Manufacturer as ManufacturerResource;
use ShopwareAdapter\DataPersister\Attribute\AttributeDataPersisterInterface;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\ServiceBus\Command\CommandInterface;
use SystemConnector\ServiceBus\Command\TransferObjectCommand;
use SystemConnector\ServiceBus\CommandHandler\CommandHandlerInterface;
use SystemConnector\ServiceBus\CommandType;
use SystemConnector\TransferObject\Manufacturer\Manufacturer;
use SystemConnector\TransferObject\Media\Media;

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
    public function supports(CommandInterface $command): bool
    {
        return $command instanceof TransferObjectCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME &&
            $command->getObjectType() === Manufacturer::TYPE &&
            $command->getCommandType() === CommandType::HANDLE;
    }

    /**
     * @param CommandInterface $command
     *
     * @throws ManufacturerNotFoundException
     * @throws ParameterMissingException
     * @throws ValidationException
     * @throws ValidationException
     * @throws ParameterMissingException
     *
     * @return bool
     */
    public function handle(CommandInterface $command): bool
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
                'objectIdentifier' => $manufacturer->getLogoIdentifier(),
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
                $identity = $this->identityService->insert(
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

            $this->identityService->insert(
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
