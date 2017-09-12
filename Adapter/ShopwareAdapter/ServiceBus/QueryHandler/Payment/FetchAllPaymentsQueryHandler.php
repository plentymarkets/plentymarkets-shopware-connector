<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\Payment;

use Exception;
use PlentyConnector\Connector\ServiceBus\Query\Payment\FetchAllPaymentsQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use Psr\Log\LoggerInterface;
use ShopwareAdapter\DataProvider\Order\OrderDataProviderInterface;
use ShopwareAdapter\ResponseParser\Payment\PaymentResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchAllPaymentsQueryHandler
 */
class FetchAllPaymentsQueryHandler implements QueryHandlerInterface
{
    /**
     * @var PaymentResponseParserInterface
     */
    private $responseParser;

    /**
     * @var OrderDataProviderInterface
     */
    private $dataProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * FetchAllPaymentsQueryHandler constructor.
     *
     * @param PaymentResponseParserInterface $responseParser
     * @param OrderDataProviderInterface $dataProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        PaymentResponseParserInterface $responseParser,
        OrderDataProviderInterface $dataProvider,
        LoggerInterface $logger
    ) {
        $this->responseParser = $responseParser;
        $this->dataProvider = $dataProvider;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllPaymentsQuery &&
            $query->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $orders = $this->dataProvider->getOpenOrders();

        $parsedElements = [];
        foreach ($orders as $order) {
            try {
                $order = $this->dataProvider->getOrderDetails($order['id']);

                $result = $this->responseParser->parse($order);
            } catch (Exception $exception) {
                $this->logger->error($exception->getMessage());

                $result = null;
            }

            if (empty($result)) {
                continue;
            }

            $parsedElements = array_filter($result);

            foreach ($parsedElements as $parsedElement) {
                $parsedElements[] = $parsedElement;
            }
        }

        return $parsedElements;
    }
}
