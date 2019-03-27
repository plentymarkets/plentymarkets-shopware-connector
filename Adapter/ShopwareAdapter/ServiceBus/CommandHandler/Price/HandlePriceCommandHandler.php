<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Price;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Shopware\Models\Article\Detail;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\ServiceBus\Command\CommandInterface;
use SystemConnector\ServiceBus\Command\TransferObjectCommand;
use SystemConnector\ServiceBus\CommandHandler\CommandHandlerInterface;
use SystemConnector\ServiceBus\CommandType;
use SystemConnector\TransferObject\Product\Price\Price;
use SystemConnector\TransferObject\Product\Variation\Variation;

class HandlePriceCommandHandler implements CommandHandlerInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        IdentityServiceInterface $identityService,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->identityService = $identityService;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof TransferObjectCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME &&
            $command->getObjectType() === Price::TYPE &&
            $command->getCommandType() === CommandType::HANDLE;
    }

    /**
     * {@inheritdoc}
     *
     * @param TransferObjectCommand $command
     */
    public function handle(CommandInterface $command)
    {
        /**
         * @var Price $price
         */
        $prices = $command->getPayload();

        $variationIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $prices->getVariationIdentifier(),
            'objectType' => Variation::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $variationIdentity) {
            $this->logger->notice('could not find variation identity - ' . $prices->getVariationIdentifier());

            return false;
        }

        $this->identityService->findOneOrCreate(
            $variationIdentity->getAdapterIdentifier(),
            ShopwareAdapter::NAME,
            Price::TYPE
        );

        $variationRespository = $this->entityManager->getRepository(Detail::class);

        /**
         * @var null|Detail $variation
         */
        $variation = $variationRespository->find($variationIdentity->getAdapterIdentifier());

        if (null === $variation) {
            $this->logger->notice('could not find variation - ' . $prices->getVariationIdentifier());

            return false;
        }

        $variation->setPrices($prices);

        $this->entityManager->persist($variation);
        $this->entityManager->flush();
        $this->entityManager->clear();

        return true;
    }
}
