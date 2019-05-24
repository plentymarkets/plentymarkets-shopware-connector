<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Price;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Shopware\Components\Api\Manager;
use Shopware\Models\Article\Price as VariantPrice;
use Shopware\Models\Customer\Customer;
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
    public function supports(CommandInterface $command): bool
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
    public function handle(CommandInterface $command): bool
    {
        /**
         * @var Price $price
         */
        $price = $command->getPayload();

        $customerGroupIdentity = $this->identityService->findOneBy(
            [
                'objectIdentifier' => $price->getCustomerGroupIdentifier(),
                'objectType' => CustomerGroup::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]
        );

        if (null === $customerGroupIdentity) {
            $this->logger->notice('could not find customergroup identity - ' . $price->getCustomerGroupIdentifier());

            return false;
        }

        /**
         * @var Customer $customerGroup
         */
        $customerGroup = $this->customerGroupDataProvider->getCustomerGroupKeyByShopwareIdentifier(
            $customerGroupIdentity->getAdapterIdentifier()
        );

        if (null === $customerGroup) {
            $this->logger->notice(
                'could not find variation CustomerGroup - ' . $customerGroupIdentity->getAdapterIdentifier()
            );

            return false;
        }

        $variationIdentity = $this->identityService->findOneBy(
            [
                'objectIdentifier' => $price->getVariationIdentifier(),
                'objectType' => Variation::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]
        );

        if (null === $variationIdentity) {
            $this->logger->notice('could not find variation identity - ' . $price->getVariationIdentifier());

            return false;
        }

        /**
         * @var Identity $priceIdentity
         */
        $priceIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $price->getIdentifier(),
            'objectType' => Price::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        $variantPrice = $this->getVariantPrice($priceIdentity, $variationIdentity, $customerGroup);

        if (null !== $variantPrice) {
            $this->entityManager->remove($variantPrice);
        }

        $resource = Manager::getResource('Variant');
        $params['__options_prices'] = ['replace' => false];
        $params['prices'][] = [
            'customerGroupKey' => $customerGroup,
            'price' => $price->getPrice(),
            'pseudoPrice' => $price->getPseudoPrice(),
            'from' => $price->getFromAmount(),
            'to' => $price->getToAmount(),
        ];

        $resource->update(
            $variationIdentity->getAdapterIdentifier(),
            $params
        );

        /**
         * @var Identity $priceIdentity
         */
        $priceIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $price->getIdentifier(),
            'objectType' => Price::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        $variantPrice = $this->getVariantPrice(null, $variationIdentity, $customerGroup);

        if (null !== $priceIdentity && null !== $variantPrice) {
            $this->identityService->update(
                $priceIdentity,
                [
                    'adapterIdentifier' => (string) $variantPrice->getId(),
                ]
            );

            return true;
        }

        $this->identityService->insert(
            (string) $price->getIdentifier(),
            Price::TYPE,
            (string) $variantPrice->getId(),
            ShopwareAdapter::NAME
        );

        return true;
    }

    /**
     * @param null|Identity $priceIdentity
     * @param Identity      $variationIdentity
     * @param Customer      $customerGroup
     *
     * @return null|VariantPrice
     */
    private function getVariantPrice($priceIdentity, Identity $variationIdentity = null, $customerGroup = null)
    {
        $repository = $this->entityManager->getRepository(VariantPrice::class);

        if (null !== $priceIdentity && null === $variationIdentity) {
            /**
             * @var null|VariantPrice $variantPrice
             */
            $variantPrice = $repository->findOneBy(
                [
                    'id' => $priceIdentity->getAdapterIdentifier(),
                ]
            );
        } else {
            /**
             * @var null|VariantPrice $swPrice
             */
            $variantPrice = $repository->findOneBy(
                [
                    'articleDetailsId' => $variationIdentity->getAdapterIdentifier(),
                    'customerGroupKey' => $customerGroup,
                ]
            );
        }

        return $variantPrice;
    }
}
