<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Media;

use Shopware\Components\Api\Manager;
use Shopware\Components\Api\Resource\Media as MediaResource;
use ShopwareAdapter\DataPersister\Attribute\AttributeDataPersisterInterface;
use ShopwareAdapter\DataProvider\Media\MediaDataProviderInterface;
use ShopwareAdapter\Helper\AttributeHelper;
use ShopwareAdapter\RequestGenerator\Media\MediaRequestGeneratorInterface;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\ServiceBus\Command\CommandInterface;
use SystemConnector\ServiceBus\Command\TransferObjectCommand;
use SystemConnector\ServiceBus\CommandHandler\CommandHandlerInterface;
use SystemConnector\ServiceBus\CommandType;
use SystemConnector\TransferObject\Media\Media;

class HandleMediaCommandHandler implements CommandHandlerInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var MediaRequestGeneratorInterface
     */
    private $mediaRequestGenerator;

    /**
     * @var MediaDataProviderInterface
     */
    private $mediaDataProvider;

    /**
     * @var AttributeHelper
     */
    private $attributeHelper;

    /**
     * @var AttributeDataPersisterInterface
     */
    private $attributePersister;

    public function __construct(
        IdentityServiceInterface $identityService,
        MediaRequestGeneratorInterface $mediaRequestGenerator,
        MediaDataProviderInterface $mediaDataProvider,
        AttributeHelper $attributeHelper,
        AttributeDataPersisterInterface $attributePersister
    ) {
        $this->identityService = $identityService;
        $this->mediaRequestGenerator = $mediaRequestGenerator;
        $this->mediaDataProvider = $mediaDataProvider;
        $this->attributeHelper = $attributeHelper;
        $this->attributePersister = $attributePersister;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof TransferObjectCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME &&
            $command->getObjectType() === Media::TYPE &&
            $command->getCommandType() === CommandType::HANDLE;
    }

    /**
     * {@inheritdoc}
     *
     * @param TransferObjectCommand $command
     */
    public function handle(CommandInterface $command)
    {
        /** @var Media $media */
        $media = $command->getPayload();

        if ($media->getHash() === $this->mediaDataProvider->getMediaHashForMediaObject($media)) {
            return true;
        }

        $this->attributeHelper->addFieldAsAttribute($media, 'alternateName');
        $this->attributeHelper->addFieldAsAttribute($media, 'name');
        $this->attributeHelper->addFieldAsAttribute($media, 'filename');
        $this->attributeHelper->addFieldAsAttribute($media, 'hash');

        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => (string) $media->getIdentifier(),
            'objectType' => Media::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        $resource = $this->getMediaResource();
        $params = $this->mediaRequestGenerator->generate($media);

        if (null !== $identity) {
            $mediaModel = $resource->update($identity->getAdapterIdentifier(), $params);
        } else {
            $mediaModel = $resource->create($params);

            $this->identityService->create(
                $media->getIdentifier(),
                Media::TYPE,
                (string) $mediaModel->getId(),
                ShopwareAdapter::NAME
            );
        }

        $this->attributePersister->saveMediaAttributes(
            $mediaModel,
            $media->getAttributes()
        );

        $this->attributePersister->saveMediaAttributes(
            $mediaModel,
            $media->getAttributes()
        );

        return true;
    }

    /**
     * @return MediaResource
     */
    private function getMediaResource()
    {
        // without this reset the entitymanager sometimes the album is not found correctly.
        Shopware()->Container()->reset('models');

        return Manager::getResource('Media');
    }
}
