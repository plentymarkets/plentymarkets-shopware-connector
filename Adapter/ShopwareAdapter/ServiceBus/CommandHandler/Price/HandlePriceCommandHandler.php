<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Price;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Shopware\Components\Api\Manager;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Price as SwPrice;
use Shopware\Models\Customer\Group;
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
use Shopware\Components\Api\Resource\Variant;


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

            /**
             * @var Identity $priceIdentity
             */
            $priceIdentity = $this->identityService->findBy([
                'objectIdentifier' => $price->getIdentifier(),
                'objectType' => Price::TYPE,
                'adapterName' => ShopwareAdapter::NAME
            ]);

        $customerGroupIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $price->getCustomerGroupIdentifier(),
            'objectType' => CustomerGroup::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $customerGroupIdentity) {
            $this->logger->notice('could not find customergroup identity - ' . $price->getCustomerGroupIdentifier());

            return false;
        }

        $customerGroupKey = $this->customerGroupDataProvider->getCustomerGroupKeyByShopwareIdentifier(
            $customerGroupIdentity->getAdapterIdentifier()
        );

        $variationIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $price->getVariationIdentifier(),
            'objectType' => Variation::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $variationIdentity) {
            $this->logger->notice('could not find variation identity - ' . $price->getVariationIdentifier());

            return false;
        }

            if (empty($priceIdentity)) {

                $priceRespository = $this->entityManager->getRepository(SwPrice::class);

                /**
                 * @var null|Detail $variation
                 */
                $swPrice = $priceRespository->findOneBy([
                    'articleDetailsId' => $variationIdentity->getAdapterIdentifier(),
                    'customerGroupKey' => $customerGroupKey
                ]);

                if (null === $swPrice) {
                    $this->logger->notice('could not find price - ' . $price->getIdentifier());

                    return false;
                }

                $this->identityService->insert(
                    $price->getIdentifier(),
                    Price::TYPE,
                    (string) $swPrice->getId(),
                    ShopwareAdapter::NAME
                );
            }else{
                $priceRespository = $this->entityManager->getRepository(SwPrice::class);

            }

        $variationResource = $this->getVariationResource();
        $variationParams['prices'][] = [
            'customerGroupKey' => $customerGroupKey,
            'price' => $price->getPrice(),
            'pseudoPrice' => $price->getPseudoPrice(),
            'from' => $price->getFromAmount(),
            'to' => $price->getToAmount(),
        ];

        $variationResource->update(
            $variationIdentity->getAdapterIdentifier(),
            $variationParams
        );

            return true;
        }

    /**
     * @return Variant
     */
    private function getVariationResource()
    {
        // without this reset the entitymanager sometimes the album is not found correctly.
        Shopware()->Container()->reset('models');

        return Manager::getResource('Variant');
    }
}
