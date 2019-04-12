<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Price;

use Psr\Log\LoggerInterface;
use Shopware\Components\Api\Manager;
use ShopwareAdapter\DataProvider\CustomerGroup\CustomerGroupDataProviderInterface;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
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
     * @var CustomerGroupDataProviderInterface
     */
    private $customerGroupDataProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        IdentityServiceInterface $identityService,
        CustomerGroupDataProviderInterface $customerGroupDataProvider,
        LoggerInterface $logger
    ) {
        $this->identityService = $identityService;
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

        $customerGroup = $this->customerGroupDataProvider->getCustomerGroupKeyByShopwareIdentifier(
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

        $resource = Manager::getResource('Variant');
        $params['__options_prices'] = ['replace' => true];
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

            return true;
        }
}
