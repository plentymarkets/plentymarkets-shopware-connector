<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Manufacturer;

use Doctrine\ORM\EntityManagerInterface;
use Shopware\Components\Api\Exception\NotFoundException as ManufacturerNotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Components\Api\Resource\Manufacturer as ManufacturerResource;
use Shopware\Models\Country\Country as CountryModel;
use Shopware\Models\Country\Repository as CountryRepository;
use ShopwareAdapter\DataPersister\Attribute\AttributeDataPersisterInterface;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\ServiceBus\Command\CommandInterface;
use SystemConnector\ServiceBus\Command\TransferObjectCommand;
use SystemConnector\ServiceBus\CommandHandler\CommandHandlerInterface;
use SystemConnector\ServiceBus\CommandType;
use SystemConnector\TransferObject\Country\Country;
use SystemConnector\TransferObject\Manufacturer\Manufacturer;
use SystemConnector\TransferObject\Media\Media;
use SystemConnector\ValueObject\Attribute\Attribute;

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
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var CountryRepository
     */
    private $countryRepository;

    public function __construct(
        ManufacturerResource $resource,
        IdentityServiceInterface $identityService,
        AttributeDataPersisterInterface $attributePersister,
        EntityManagerInterface $entityManager
    ) {
        $this->resource = $resource;
        $this->identityService = $identityService;
        $this->attributePersister = $attributePersister;
        $this->entityManager = $entityManager;
        $this->countryRepository = $entityManager->getRepository(CountryModel::class);
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

        $attributes = $manufacturer->getAttributes();

        /** @var null|Attribute $countryIdentifierAttribute */
        $countryIdentifierAttribute = array_filter($manufacturer->getAttributes(), function (Attribute $attribute) {
            return $attribute->getKey() === 'countryIdentifier';
        })[8] ?? null;

        if (null !== $countryIdentifierAttribute) {
            $countryNameAttribute = $this->getCountryNameAsAttribute($countryIdentifierAttribute->getValue());

            if (null !== $countryNameAttribute) {
                $attributes[] = $countryNameAttribute;
            }
        }

        $manufacturer->setAttributes($attributes);

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
            'objectIdentifier' => $manufacturer->getIdentifier(),
            'objectType' => Manufacturer::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $identity) {
            $existingManufacturer = $this->findExistingManufacturer($manufacturer);

            if (null !== $existingManufacturer) {
                $identity = $this->identityService->insert(
                    $manufacturer->getIdentifier(),
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
                $manufacturer->getIdentifier(),
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

    /**
     * @param string $objectIdentifier
     *
     * @return null|Attribute
     */
    private function getCountryNameAsAttribute(string $objectIdentifier): ?Attribute
    {
        $countryIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $objectIdentifier,
            'objectType' => Country::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $countryIdentity) {
            return null;
        }

        /**
         * @var Country $country
         */
        $country = $this->countryRepository->find($countryIdentity->getAdapterIdentifier());

        $countryNameAttribute = new Attribute();
        $countryNameAttribute->setKey('countryName');
        $countryNameAttribute->setValue($country->getName());
        $countryNameAttribute->setType('text');

        return $countryNameAttribute;
    }
}
