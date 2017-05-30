<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Media;

use PlentyConnector\Adapter\ShopwareAdapter\Helper\AttributeHelper;
use PlentyConnector\Connector\IdentityService\Exception\NotFoundException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\HandleCommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\Media\HandleMediaCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\TransferObject\Media\Media;
use PlentyConnector\Connector\TransferObject\MediaCategory\MediaCategory;
use Shopware\Bundle\MediaBundle\MediaService;
use Shopware\Components\Api\Exception\NotFoundException as MediaNotFoundException;
use Shopware\Components\Api\Resource\Media as MediaResource;
use Shopware\Models\Media\Album;
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
     * @var MediaService
     */
    private $mediaService;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var AttributeHelper
     */
    private $attributeHelper;

    /**
     * HandleMediaCommandHandler constructor.
     *
     * @param MediaResource            $resource
     * @param MediaService             $mediaService
     * @param IdentityServiceInterface $identityService
     * @param AttributeHelper          $attributeHelper
     */
    public function __construct(
        MediaResource $resource,
        MediaService $mediaService,
        IdentityServiceInterface $identityService,
        AttributeHelper $attributeHelper
    ) {
        $this->resource = $resource;
        $this->mediaService = $mediaService;
        $this->identityService = $identityService;
        $this->attributeHelper = $attributeHelper;
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

        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => (string) $media->getIdentifier(),
            'objectType' => Media::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        $params = [
            'album' => Album::ALBUM_ARTICLE,
            'file' => $media->getContent(),
            'description' => $media->getName(),
        ];

        $this->attributeHelper->addFieldAsAttribute($media, 'alternateName');

        if (null !== $media->getMediaCategoryIdentifier()) {
            $mediaCategoryIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => $media->getMediaCategoryIdentifier(),
                'objectType' => MediaCategory::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            if (null === $mediaCategoryIdentity) {
                throw new NotFoundException('Missing Media Category for Adapter');
            }

            $params['album'] = $mediaCategoryIdentity->getAdapterIdentifier();
        }

        if (null !== $identity) {
            try {
                $mediaObject = $this->resource->getOne($identity->getAdapterIdentifier());

                if (!$this->mediaService->has($mediaObject['path'])) {
                    $this->identityService->remove($identity);

                    $identity = null;
                }
            } catch (MediaNotFoundException $exception) {
                $this->identityService->remove($identity);

                $identity = null;
            }
        }

        if (null === $identity) {
            $mediaModel = $this->resource->create($params);

            $identity = $this->identityService->create(
                $media->getIdentifier(),
                Media::TYPE,
                (string) $mediaModel->getId(),
                ShopwareAdapter::NAME
            );
        } else {
            unset($params['file']);

            $this->resource->update($identity->getAdapterIdentifier(), $params);
        }

        $this->attributeHelper->saveAttributes(
            (int) $identity->getAdapterIdentifier(),
            $media->getAttributes(),
            's_media_attributes'
        );

        return true;
    }
}
