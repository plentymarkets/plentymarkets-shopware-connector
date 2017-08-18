<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Media;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\HandleCommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\Media\HandleMediaCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\TransferObject\Media\Media;
use PlentyConnector\Connector\ValueObject\Identity\Identity;
use Shopware\Components\Api\Exception\NotFoundException as MediaNotFoundException;
use Shopware\Components\Api\Resource\Media as MediaResource;
use ShopwareAdapter\DataPersister\Attribute\AttributeDataPersisterInterface;
use ShopwareAdapter\DataProvider\Media\MediaDataProviderInterface;
use ShopwareAdapter\Helper\AttributeHelper;
use ShopwareAdapter\RequestGenerator\Media\MediaRequestGeneratorInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class HandleMediaCommandHandler.
 */
class HandleMediaCommandHandler implements CommandHandlerInterface
{
    /**
     * @var MediaResource
     */
    private $resource;

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

    /**
     * HandleMediaCommandHandler constructor.
     *
     * @param MediaResource                   $resource
     * @param IdentityServiceInterface        $identityService
     * @param MediaRequestGeneratorInterface  $mediaRequestGenerator
     * @param MediaDataProviderInterface      $mediaDataProvider
     * @param AttributeHelper                 $attributeHelper
     * @param AttributeDataPersisterInterface $attributePersister
     */
    public function __construct(
        MediaResource $resource,
        IdentityServiceInterface $identityService,
        MediaRequestGeneratorInterface $mediaRequestGenerator,
        MediaDataProviderInterface $mediaDataProvider,
        AttributeHelper $attributeHelper,
        AttributeDataPersisterInterface $attributePersister
    ) {
        $this->resource = $resource;
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
        return $command instanceof HandleMediaCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(CommandInterface $command)
    {
        /**
         * @var HandleCommandInterface $command
         * @var Media                  $media
         */
        $media = $command->getTransferObject();

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

        if (null !== $identity) {
            try {
                $this->resource->delete($identity->getAdapterIdentifier());
            } catch (MediaNotFoundException $exception) {
                // fail silently
            }

            $identities = $this->identityService->findBy([
                'objectIdentifier' => $identity->getObjectIdentifier(),
                'objectType' => Media::TYPE,
                'adapterIdentifier' => $identity->getAdapterIdentifier(),
                'adapterName' => $identity->getAdapterName(),
            ]);

            array_walk($identities, function (Identity $identity) {
                $this->identityService->remove($identity);
            });
        }

        $params = $this->mediaRequestGenerator->generate($media);
        $mediaModel = $this->resource->create($params);

        $this->identityService->create(
            $media->getIdentifier(),
            Media::TYPE,
            (string) $mediaModel->getId(),
            ShopwareAdapter::NAME
        );

        $this->attributePersister->saveMediaAttributes(
            $mediaModel,
            $media->getAttributes()
        );

        return true;
    }
}
