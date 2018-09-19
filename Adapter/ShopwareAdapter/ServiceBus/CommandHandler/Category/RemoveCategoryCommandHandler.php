<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Category;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\TransferObjectCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\ServiceBus\CommandType;
use PlentyConnector\Connector\TransferObject\Category\Category;
use PlentyConnector\Connector\ValueObject\Identity\Identity;
use Psr\Log\LoggerInterface;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Manager;
use Shopware\Components\Api\Resource\Category as CategoryResource;
use ShopwareAdapter\ShopwareAdapter;

class RemoveCategoryCommandHandler implements CommandHandlerInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        IdentityServiceInterface $identityService,
        LoggerInterface $logger
    ) {
        $this->identityService = $identityService;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof TransferObjectCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME &&
            $command->getObjectType() === Category::TYPE &&
            $command->getCommandType() === CommandType::REMOVE;
    }

    /**
     * {@inheritdoc}
     *
     * @param TransferObjectCommand $command
     */
    public function handle(CommandInterface $command)
    {
        $identifier = $command->getPayload();

        $identities = $this->identityService->findBy([
            'objectIdentifier' => (string) $identifier,
            'objectType' => Category::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $identities) {
            $this->logger->notice('no matching identity found', ['command' => $command]);

            return false;
        }

        array_walk($identities, function (Identity $identity) use ($command) {
            $resource = $this->getCategoryResource();

            try {
                $resource->delete($identity->getAdapterIdentifier());
            } catch (NotFoundException $exception) {
                $this->logger->notice('identity removed but the object was not found', ['command' => $command]);
            }

            $this->identityService->remove($identity);
        });

        return true;
    }

    /**
     * @return CategoryResource
     */
    private function getCategoryResource()
    {
        // without this reset the entitymanager sometimes the album is not found correctly.
        Shopware()->Container()->reset('models');

        return Manager::getResource('Category');
    }
}
