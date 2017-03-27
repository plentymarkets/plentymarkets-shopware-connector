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
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * HandleMediaCommandHandler constructor.
     *
     * @param MediaResource            $resource
     * @param IdentityServiceInterface $identityService
     */
    public function __construct(MediaResource $resource, IdentityServiceInterface $identityService)
    {
        $this->resource = $resource;
        $this->identityService = $identityService;
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
     * TODO: only update if hash has changed - add hash attribute to media.
     *
     * {@inheritdoc}
     */
    public function handle(CommandInterface $command)
    {
        /**
         * @var HandleCommandInterface
         * @var Media                  $media
         */
        $media = $command->getTransferObject();

        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => (string) $media->getIdentifier(),
            'objectType'       => Media::TYPE,
            'adapterName'      => ShopwareAdapter::NAME,
        ]);

        $params = [
            'album'       => Album::ALBUM_ARTICLE,
            'file'        => $media->getLink(),
            'description' => $media->getName(),
        ];

        if (null !== $media->getMediaCategoryIdentifier()) {
            $mediaCategoryIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => $media->getMediaCategoryIdentifier(),
                'objectType'       => MediaCategory::TYPE,
                'adapterName'      => ShopwareAdapter::NAME,
            ]);

            if (null === $mediaCategoryIdentity) {
                throw new NotFoundException('Missing Media Category for Adapter');
            }

            $params['album'] = $mediaCategoryIdentity->getAdapterIdentifier();
        }

        if (null !== $identity) {
            try {
                $this->resource->getOne($identity->getAdapterIdentifier());
            } catch (\Shopware\Components\Api\Exception\NotFoundException $notFoundException) {
                $this->identityService->remove($identity);
                $identity = null;
            }
        }

        if (null === $identity) {
            $mediaModel = $this->resource->create($params);

            $this->identityService->create(
                $media->getIdentifier(),
                Media::TYPE,
                (string) $mediaModel->getId(),
                ShopwareAdapter::NAME
            );
        } else {
            unset($params['file']);

            $this->resource->update($identity->getAdapterIdentifier(), $params);
        }

        return true;
    }
}
