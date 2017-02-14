<?php

namespace PlentymarketsAdapter\ServiceBus\CommandHandler\Order;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\HandleCommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\Order\HandleOrderCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentyConnector\Connector\TransferObject\Order\OrderInterface;
use PlentyConnector\Connector\TransferObject\OrderItem\OrderItemInterface;
use PlentyConnector\Connector\TransferObject\OrderStatus\OrderStatus;
use PlentyConnector\Connector\TransferObject\ShippingProfile\ShippingProfile;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;

/**
 * Class HandleOrderCommandHandler.
 */
class HandleOrderCommandHandler implements CommandHandlerInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * HandleOrderCommandHandler constructor.
     *
     * @param ClientInterface $client
     * @param IdentityServiceInterface $identityService
     */
    public function __construct(ClientInterface $client, IdentityServiceInterface $identityService)
    {
        $this->client = $client;
        $this->identityService = $identityService;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof HandleOrderCommand &&
            $command->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(CommandInterface $command)
    {
        /**
         * @var HandleCommandInterface $command
         * @var OrderInterface $order
         */
        $order = $command->getTransferObject();

        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $order->getIdentifier(),
            'objectType' => Order::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        $orderStatusIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $order->getOrderStatusId(),
            'objectType' => OrderStatus::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        $shopIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $order->getShopId(),
            'objectType' => Shop::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        $shippingProfileIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $order->getShippingProfileId(),
            'objectType' => ShippingProfile::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        $params = [];

        if ($identity === null) {
            // create new order
            $params['typeId'] = 1; // TODO change to support different types
            $params['statusId'] = $orderStatusIdentity->getAdapterIdentifier();
            $params['plentyId'] = $shopIdentity->getAdapterIdentifier();

            $params['orderItems'] = array_map(function ($item) use ($shippingProfileIdentity) {
                /**
                 * @var OrderItemInterface $item
                 */
                $itemParams = [];

                $itemParams['orderItemName'] = $item->getName();
                $itemParams['quantity'] = $item->getQuantity();
                $itemParams['shippingProfileId'] = $shippingProfileIdentity->getAdapterIdentifier();

                // TODO remove placeholders
                $itemParams['referrerId'] = 1;
                $itemParams['itemVariationId'] = 1001;
                $itemParams['countryVatId'] = 1;
                $itemParams['vatField'] = 0;
                $itemParams['vatRate'] = 19;
                $itemParams['amounts'] = [
                    'isSystemCurrency' => true,
                    'currency' => 'EUR',
                    'exchangeRate' => 1,
                    'priceOriginalGross' => $item->getPrice(),
                ];

                // WAREHOUSE, see OrderItemProperty on https://developers.plentymarkets.com/api-doc/Order#orderitem_models_order
                $itemParams['properties'] = [
                    [
                        'typeId' => 1,
                        'value' => '1',
                    ],
                ];

                // TODO product and variation

                $itemParams['typeId'] = 1; // TODO same issue as above

                return $itemParams;
            }, $order->getOrderItems());

            $result = $this->client->request('post', 'orders', $params);
        }
        // TODO update existing order

        return true;
    }
}
