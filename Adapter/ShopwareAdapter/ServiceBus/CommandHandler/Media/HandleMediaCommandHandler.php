<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Media;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PlentyConnector\Adapter\ShopwareAdapter\Helper\AttributeHelper;
use PlentyConnector\Connector\IdentityService\Exception\NotFoundException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\HandleCommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\Media\HandleMediaCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\TransferObject\Media\Media;
use PlentyConnector\Connector\TransferObject\MediaCategory\MediaCategory;
use RuntimeException;
use Shopware\Bundle\MediaBundle\MediaService;
use Shopware\Components\Api\Resource\Media as MediaResource;
use Shopware\Models\Media\Album as AlbumModel;
use Shopware\Models\Media\Media as MediaModel;
use Shopware\Models\Media\Repository as MediaRepository;
use ShopwareAdapter\ShopwareAdapter;
use Symfony\Component\HttpFoundation\File\File;

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

        /**
         * @var EntityManagerInterface $entityManager
         */
        $entityManager = Shopware()->Container()->get('models');

        $this->attributeHelper->addFieldAsAttribute($media, 'alternateName');
        $this->attributeHelper->addFieldAsAttribute($media, 'hash');

        $mediaObject = $this->getExistingMedia($media);

        if (null === $mediaObject) {
            $mediaObject = new MediaModel();
            $mediaObject->setCreated(new \DateTime());
            $mediaObject->setUserId(0);
        }

        if (null !== $media->getName()) {
            $mediaObject->setName($media->getName());
        } else {
            $mediaObject->setName($media->getFilename());
        }

        $mediaObject->setDescription('');

        $mediaObject->setAlbum($this->getAlbum($media));

        $file = $this->uploadFile($media);
        //$mediaObject->setFile($file);

        $entityManager->persist($mediaObject);
        $entityManager->flush();

        if (null === $identity) {
            $identity = $this->identityService->create(
                $media->getIdentifier(),
                Media::TYPE,
                (string) $mediaObject->getId(),
                ShopwareAdapter::NAME
            );
        }

        $this->attributeHelper->saveAttributes(
            (int) $identity->getAdapterIdentifier(),
            $media->getAttributes(),
            's_media_attributes'
        );

        return true;
    }

    /**
     * @param Media $media
     *
     * @return null|MediaModel
     */
    private function getExistingMedia(Media $media)
    {
        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => (string) $media->getIdentifier(),
            'objectType' => Media::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $identity) {
            return null;
        }

        /**
         * @var EntityManagerInterface $entityManager
         */
        $entityManager = Shopware()->Container()->get('models');

        /**
         * @var MediaRepository $mediaRepository
         */
        $mediaRepository = $entityManager->getRepository(MediaModel::class);

        /**
         * @var null|MediaModel $mediaObject
         */
        $mediaObject = $mediaRepository->find($identity->getAdapterIdentifier());

        if (null === $mediaObject) {
            $this->identityService->remove($identity);

            return null;
        }

        return $mediaObject;
    }

    /**
     * @param Media $media
     *
     * @return File
     */
    private function uploadFile(Media $media)
    {
        $path = Shopware()->DocPath('media_' . 'temp');
        $filePath = tempnam($path, 'PlentyConnector') . $media->getFilename();

        file_put_contents($filePath, base64_decode($media->getContent()));

        return new File($filePath, true);
    }

    /**
     * @param Media $media
     *
     * @throws NotFoundException
     *
     * @return null|AlbumModel
     */
    private function getAlbum(Media $media)
    {
        if (null === $media->getMediaCategoryIdentifier()) {
            throw new NotFoundException('missing media category');
        }

        /**
         * @var EntityManagerInterface $entityManager
         */
        $entityManager = Shopware()->Container()->get('models');

        $mediaCategoryIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $media->getMediaCategoryIdentifier(),
            'objectType' => MediaCategory::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $mediaCategoryIdentity) {
            throw new NotFoundException('missing media category');
        }

        /**
         * @var EntityRepository $albumRepository
         */
        $albumRepository = $entityManager->getRepository(AlbumModel::class);

        /**
         * @var null|AlbumModel $album
         */
        $album = $albumRepository->find($mediaCategoryIdentity->getAdapterIdentifier());

        if (null === $album) {
            throw new RuntimeException('invalid media category');
        }

        return $album;
    }
}
