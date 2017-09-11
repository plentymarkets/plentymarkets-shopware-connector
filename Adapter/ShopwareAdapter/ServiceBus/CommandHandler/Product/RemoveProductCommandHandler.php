<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Product;

use Exception;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\Product\RemoveProductCommand;
use PlentyConnector\Connector\ServiceBus\Command\RemoveCommandInterface;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\TransferObject\Product\Product;
use PlentyConnector\Connector\ValueObject\Identity\Identity;
use Psr\Log\LoggerInterface;
use Shopware\Components\Api\Resource\Article;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class RemoveProductCommandHandler.
 */
class RemoveProductCommandHandler implements CommandHandlerInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * RemoveProductCommandHandler constructor.
     *
     * @param IdentityServiceInterface $identityService
     * @param LoggerInterface          $logger
     */
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
        return $command instanceof RemoveProductCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(CommandInterface $command)
    {
        /**
         * @var RemoveCommandInterface $command
         */
        $identifier = $command->getObjectIdentifier();

        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => (string) $identifier,
            'objectType' => Product::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $identity) {
            $this->logger->notice('no matching identity found', ['command' => $command]);

            return false;
        }

        try {
            $resource = $this->getArticleResource();
            $resource->delete($identity->getAdapterIdentifier());
        } catch (Exception $exception) {
            $this->logger->notice('identity removed but the object was not found', ['command' => $command]);
        }

        $identities = $this->identityService->findBy([
            'objectIdentifier' => $identifier,
        ]);

        array_walk($identities, function (Identity $identity) {
            $this->identityService->remove($identity);
        });

        return true;
    }

    /**
     * @return Article
     */
    private function getArticleResource()
    {
        Shopware()->Container()->reset('models');

        return Manager::getResource('Article');
    }
}
