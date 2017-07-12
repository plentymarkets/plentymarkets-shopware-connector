<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Media;

use PlentyConnector\Connector\IdentityService\Exception\NotFoundException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\HandleCommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\Media\HandleMediaCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\TransferObject\Media\Media;
use PlentyConnector\Connector\TransferObject\MediaCategory\MediaCategory;
use PlentyConnector\Connector\ValueObject\Identity\Identity;
use Shopware\Components\Api\Exception\NotFoundException as MediaNotFoundException;
use Shopware\Components\Api\Resource\Media as MediaResource;
use Shopware\Models\Media\Album;
use ShopwareAdapter\Helper\AttributeHelper;
use ShopwareAdapter\ShopwareAdapter;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
     * @var AttributeHelper
     */
    private $attributeHelper;

    /**
     * HandleMediaCommandHandler constructor.
     *
     * @param MediaResource            $resource
     * @param IdentityServiceInterface $identityService
     * @param AttributeHelper          $attributeHelper
     */
    public function __construct(
        MediaResource $resource,
        IdentityServiceInterface $identityService,
        AttributeHelper $attributeHelper
    ) {
        $this->resource = $resource;
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

        $params = [
            'album' => Album::ALBUM_ARTICLE,
            'file' => $this->uploadFile($media),
            'description' => $media->getName(),
        ];

        $this->attributeHelper->addFieldAsAttribute($media, 'alternateName');
        $this->attributeHelper->addFieldAsAttribute($media, 'name');
        $this->attributeHelper->addFieldAsAttribute($media, 'filename');
        $this->attributeHelper->addFieldAsAttribute($media, 'hash');

        if (null !== $media->getMediaCategoryIdentifier()) {
            $mediaCategoryIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => $media->getMediaCategoryIdentifier(),
                'objectType' => MediaCategory::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            if (null === $mediaCategoryIdentity) {
                throw new NotFoundException('missing media category for adapter');
            }

            $params['album'] = $mediaCategoryIdentity->getAdapterIdentifier();
        }

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

        $mediaModel = $this->resource->create($params);

        $this->identityService->create(
            $media->getIdentifier(),
            Media::TYPE,
            (string) $mediaModel->getId(),
            ShopwareAdapter::NAME
        );

        $this->attributeHelper->saveAttributes(
            (int) $mediaModel->getId(),
            $media->getAttributes(),
            's_media_attributes'
        );

        return true;
    }

    /**
     * @param Media $media
     *
     * @return UploadedFile
     */
    private function uploadFile(Media $media)
    {
        $path = Shopware()->DocPath('media_' . 'temp');
        $filePath = tempnam($path, 'PlentyConnector') . $media->getFilename();

        file_put_contents($filePath, base64_decode($media->getContent()));

        return new UploadedFile($filePath, $media->getFilename());
    }
}
