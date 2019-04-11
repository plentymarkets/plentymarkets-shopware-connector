<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Price;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Price as variantPrice;
use ShopwareAdapter\DataProvider\CustomerGroup\CustomerGroupDataProviderInterface;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\IdentityService\Struct\Identity;
use SystemConnector\ServiceBus\Command\CommandInterface;
use SystemConnector\ServiceBus\Command\TransferObjectCommand;
use SystemConnector\ServiceBus\CommandHandler\CommandHandlerInterface;
use SystemConnector\ServiceBus\CommandType;
use SystemConnector\TransferObject\CustomerGroup\CustomerGroup;
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
     * @var CustomerGroupDataProviderInterface
     */
    private $customerGroupDataProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        IdentityServiceInterface $identityService,
        EntityManagerInterface $entityManager,
        CustomerGroupDataProviderInterface $customerGroupDataProvider,
        LoggerInterface $logger
    ) {
        $this->identityService = $identityService;
        $this->entityManager = $entityManager;
        $this->customerGroupDataProvider = $customerGroupDataProvider;
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
        $price = $command->getPayload();

        $customerGroupIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $price->getCustomerGroupIdentifier(),
            'objectType' => CustomerGroup::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $customerGroupIdentity) {
            $this->logger->notice('could not find customergroup identity - ' . $price->getCustomerGroupIdentifier());

            return false;
        }

        $customerGroup = $this->customerGroupDataProvider->getCustomerGroupByShopwareIdentifier(
            $customerGroupIdentity->getAdapterIdentifier()
        );

        if (null === $customerGroup) {
            $this->logger->notice('could not find variation CustomerGroup - ' . $customerGroupIdentity->getAdapterIdentifier());

            return false;
        }

        $variationIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $price->getVariationIdentifier(),
            'objectType' => Variation::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $variationIdentity) {
            $this->logger->notice('could not find variation identity - ' . $price->getVariationIdentifier());

            return false;
        }

        /**
         * @var Identity $priceIdentity
         */
        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $price->getIdentifier(),
            'objectType' => Price::TYPE,
            'adapterName' => ShopwareAdapter::NAME
        ]);
        $repository = $this->entityManager->getRepository(variantPrice::class);

        if (null !== $identity) {
            /**
             * @var null|variantPrice $variantPrice
             */
            $variantPrice = $repository->findOneBy(
                [
                    'id' => $identity->getAdapterIdentifier()
                ]
            );

            if (null === $variantPrice) {
                $variantPrice = $repository->findOneBy(
                    [
                        'articleDetailsId' => $variationIdentity->getAdapterIdentifier(),
                        'customerGroupKey' => $customerGroup->getKey()
                    ]
                );
                if (null === $variantPrice) {
                    $this->logger->notice('could not find Price with identity - ' . $identity->getAdapterIdentifier());

                    return false;
                }

                $this->identityService->update(
                    $identity,
                    [
                        'adapterIdentifier' => (string) $variantPrice->getId(),
                    ]
                );
            }

            $variantPrice->setPrice($price->getPrice());
            $variantPrice->setPseudoPrice($price->getPseudoPrice());
            $variantPrice->setCustomerGroup($customerGroup);
            $variantPrice->setFrom($price->getFromAmount());
            $variantPrice->setTo($price->getToAmount());

            $this->entityManager->persist($variantPrice);
            $this->entityManager->flush();
            $this->entityManager->clear();

            return true;
        }

        /**
         * @var null|variantPrice $swPrice
         */
        $variantPrice = $repository->findOneBy(
            [
                'articleDetailsId' => $variationIdentity->getAdapterIdentifier(),
                'customerGroupKey' => $customerGroup->getKey()
            ]
        );

        if (null === $variantPrice) {
            $detailRepository = $this->entityManager->getRepository(Detail::class);
            $variation = $detailRepository->find($variationIdentity->getAdapterIdentifier());

            if (null === $detailRepository) {
                $this->logger->notice('could not find variation with identity - ' . $variationIdentity->getAdapterIdentifier());

                return false;
            }

            $variantPrice = new variantPrice();
            $variantPrice->setDetail($variation);
            $variantPrice->setArticle($variation->getArticle());
        }

        $variantPrice->setPrice($price->getPrice());
        $variantPrice->setPseudoPrice($price->getPseudoPrice());
        $variantPrice->setCustomerGroup($customerGroup);
        $variantPrice->setFrom($price->getFromAmount());
        $variantPrice->setTo( $price->getToAmount());

        $this->entityManager->persist($variantPrice);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->identityService->findOneOrCreate(
            (string) $variantPrice->getId(),
            ShopwareAdapter::NAME,
            Price::TYPE
        );

        return true;
    }
}
