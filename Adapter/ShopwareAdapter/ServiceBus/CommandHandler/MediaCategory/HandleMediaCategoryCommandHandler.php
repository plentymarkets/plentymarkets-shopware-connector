<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\MediaCategory;

use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\TransferObjectCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\ServiceBus\CommandType;
use PlentyConnector\Connector\TransferObject\MediaCategory\MediaCategory;
use Shopware\Models\Media\Album;
use Shopware\Models\Media\Settings;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class HandleMediaCategoryCommandHandler.
 */
class HandleMediaCategoryCommandHandler implements CommandHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * HandleMediaCategoryCommandHandler constructor.
     *
     * @param EntityManagerInterface   $entityManager
     * @param IdentityServiceInterface $identityService
     */
    public function __construct(EntityManagerInterface $entityManager, IdentityServiceInterface $identityService)
    {
        $this->entityManager = $entityManager;
        $this->identityService = $identityService;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof TransferObjectCommand &&
            ShopwareAdapter::NAME === $command->getAdapterName() &&
            MediaCategory::TYPE === $command->getObjectType() &&
            CommandType::HANDLE === $command->getCommandType();
    }

    /**
     * {@inheritdoc}
     *
     * @param TransferObjectCommand $command
     */
    public function handle(CommandInterface $command)
    {
        /**
         * @var MediaCategory $mediaCategory
         */
        $mediaCategory = $command->getPayload();

        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => (string) $mediaCategory->getIdentifier(),
            'objectType' => MediaCategory::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        $albumRepository = $this->entityManager->getRepository(Album::class);
        $settingsRepository = $this->entityManager->getRepository(Settings::class);

        $parent = $albumRepository->findOneBy([
            'name' => 'PlentyConnector',
        ]);

        $parentSettings = $settingsRepository->findOneBy([
            'albumId' => Album::ALBUM_ARTICLE,
        ]);

        if (null === $parent) {
            $parent = new Album();
            $parent->setName('PlentyConnector');
            $parent->setPosition(10);

            $settings = new Settings();
            $settings->setAlbum($parent);
            $settings->setCreateThumbnails($parentSettings->getCreateThumbnails());
            $settings->setThumbnailSize($parentSettings->getThumbnailSize());
            $settings->setIcon('sprite-pictures');
            $settings->setThumbnailHighDpi($parentSettings->isThumbnailHighDpi());
            $settings->setThumbnailQuality($parentSettings->getThumbnailQuality());
            $settings->setThumbnailHighDpiQuality($parentSettings->getThumbnailHighDpiQuality());

            $parent->setSettings($settings);

            $this->entityManager->persist($settings);
            $this->entityManager->persist($parent);

            $this->entityManager->flush();
            $this->entityManager->clear();
        }

        if (null === $identity) {
            $this->createNewAlbum($mediaCategory, $parent, $parentSettings);

            return true;
        }

        $album = $albumRepository->find($identity->getAdapterIdentifier());

        if (null !== $album) {
            $album->setName($mediaCategory->getName());

            $this->entityManager->persist($album);
            $this->entityManager->flush();
        } else {
            $this->createNewAlbum($mediaCategory, $parent, $parentSettings);
        }

        return true;
    }

    /**
     * @param MediaCategory $mediaCategory
     * @param Album         $parent
     * @param Settings      $parentSettings
     */
    private function createNewAlbum(MediaCategory $mediaCategory, Album $parent, Settings $parentSettings)
    {
        $connection = $this->entityManager->getConnection();

        $query = 'SELECT max(position) FROM s_media_album WHERE parentId = ?';
        $position = $connection->fetchColumn($query, [$parent->getId()]);

        $album = new Album();
        $album->setParent($parent);
        $album->setName($mediaCategory->getName());
        $album->setPosition((int) $position + 1);

        $settings = new Settings();
        $settings->setAlbum($album);
        $settings->setCreateThumbnails($parentSettings->getCreateThumbnails());
        $settings->setThumbnailSize($parentSettings->getThumbnailSize());
        $settings->setIcon('sprite-inbox');
        $settings->setThumbnailHighDpi($parentSettings->isThumbnailHighDpi());
        $settings->setThumbnailQuality($parentSettings->getThumbnailQuality());
        $settings->setThumbnailHighDpiQuality($parentSettings->getThumbnailHighDpiQuality());

        $album->setSettings($settings);

        $this->entityManager->persist($album);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->identityService->create(
            $mediaCategory->getIdentifier(),
            MediaCategory::TYPE,
            (string) $album->getId(),
            ShopwareAdapter::NAME
        );
    }
}
