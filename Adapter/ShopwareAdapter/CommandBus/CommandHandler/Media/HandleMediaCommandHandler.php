<?php

namespace ShopwareAdapter\CommandBus\CommandHandler\Media;

use PlentyConnector\Connector\CommandBus\Command\CommandInterface;
use PlentyConnector\Connector\CommandBus\Command\HandleCommandInterface;
use PlentyConnector\Connector\CommandBus\Command\Media\HandleMediaCommand;
use PlentyConnector\Connector\CommandBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Media\Media;
use PlentyConnector\Connector\TransferObject\Media\MediaInterface;
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
     * @param MediaResource $resource
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
        return
            $command instanceof HandleMediaCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * @param CommandInterface $command
     *
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Exception
     */
    public function handle(CommandInterface $command)
    {
        /**
         * @var HandleCommandInterface $command
         * @var MediaInterface $media
         */
        $media = $command->getTransferObject();

        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $media->getIdentifier(),
            'objectType' => Media::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        $params = [
            'album' => Album::ALBUM_ARTICLE,
            'file' => $media->getLink(),
            'description' => $media->getName(),
        ];

        if (null === $identity) {
            $mediaModel = $this->resource->create($params);

            $this->identityService->create(
                $media->getIdentifier(),
                Media::TYPE,
                (string)$mediaModel->getId(),
                ShopwareAdapter::NAME
            );
        } else {
            unset($params['file']);

            $this->resource->update($identity->getAdapterIdentifier(), $params);
        }
    }
}
