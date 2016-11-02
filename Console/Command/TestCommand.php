<?php

namespace PlentyConnector\Console\Command;

use Exception;
use Monolog\Logger;
use PlentyConnector\Logger\ConsoleHandler;
use PlentyConnector\Mapping\MappingServiceInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use RemotePlentymarketsAdapter\Client\ClientInterface;
use RemotePlentymarketsAdapter\Resource\ManufacturerResource;
use RemotePlentymarketsAdapter\Resource\ProductResource;
use RemotePlentymarketsAdapter\Resource\StockResource;
use Shopware\Commands\ShopwareCommand;
use Shopware\Components\Api\Manager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TestCommand
 *
 * @package PlentyConnector\Console\Command
 */
class TestCommand extends ShopwareCommand
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * TestCommand constructor.
     *
     * @param ClientInterface $client
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;

        parent::__construct();
    }

    /**
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('plentyconnector:test');
        $this->setDescription('test');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        /**
         * @var Logger $logger
         */
        $logger = $this->container->get('plentyconnector.logger');
        $logger->pushHandler(new ConsoleHandler($output));

        // /rest/stockmanagement/warehouse/
        // /rest/stockmanagement/warehouse/{warehouseId}/stock

        /**
         * @var MappingServiceInterface $service
         */
        $service = Shopware()->Container()->get('plentyconnector.mapping_service');
    }

    protected function createOrderRequestManually()
    {
        $orderResource = Manager::getResource('order');
        $orders = $orderResource->getList(0, null)['data'];

        // ignore cancelled orders
        $orders = array_filter($orders, function ($item) {
            return $item['orderStatusId'] != -1;
        });

        $order = $orderResource->getOne($orders[0]['id']);


        // TODO mapping
        $mappedPaymentId = $order['paymentId'];
        $mappedDispatchId = $order['dispatchId'];
        $mappedShopId = $order['shopId'];
        $mappedStatusId = $order['orderStatusId'];
        $mappedPaymentStatusId = $order['paymentStatusId'];
        $mappedCustomerId = $order['customerId']; // /rest/accounts

        $mappedCurrency = $order['currency'];
        $mappedExchangeRate = $order['currencyFactor'];

        $plentyOrderItems = array_map(function ($detail) use ($mappedDispatchId, $mappedCurrency, $mappedExchangeRate) {
            // TODO shopware variants are ignored
            // article detail id, get with articleId and articleNumber
            $mappedVariantId = $detail['articleId'];

            return [
                'typeId' => 1, // https://developers.plentymarkets.com/api-doc/Order#orderitem_models_order
                'itemVariationId' => $mappedVariantId,
                'shippingProfileId' => $mappedDispatchId,
                'quantity' => $detail['quantity'],
                'orderItemName' => $detail['articleName'],
                'vatRate' => $detail['taxRate'], // TODO countryVatId and vatField
                'amounts' => [
                    [
                        'currency' => $mappedCurrency,
                        'exchangeRate' => $mappedExchangeRate,
                        'priceOriginalGross' => $detail['price']
                        // TODO surcharge / discount? from Customer Group
                    ]
                ],
            ];
        }, $order['details']);

        $plentyOrder = [
            'properties' => [
                [
                    'typeId' => 14,
                    'subTypeId' => 6,
                    'value' => $order['number']
                ],
                [
                    'typeId' => 13,
                    'subTypeId' => 1,
                    'value' => $mappedPaymentId
                ],
                [
                    'typeId' => 13,
                    'subTypeId' => 3,
                    'value' => $mappedPaymentStatusId
                ]
            ],
            'orderItems' => $plentyOrderItems,
            'plentyId' => $mappedShopId,
            'statusId' => $mappedStatusId
            // TODO addresses
        ];
    }
}
