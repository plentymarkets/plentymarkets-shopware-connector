<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Order;

use PlentyConnector\Connector\ServiceBus\Query\Order\FetchChangedOrdersQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ReadApi\Order\Order;
use PlentymarketsAdapter\ResponseParser\Order\OrderResponseParserInterface;
use PlentymarketsAdapter\ServiceBus\ChangedDateTimeTrait;

/**
 * Class FetchChangedOrdersQueryHandler
 */
class FetchChangedOrdersQueryHandler implements QueryHandlerInterface
{
    use ChangedDateTimeTrait;

    /**
     * @var Order
     */
    private $api;

    /**
     * @var OrderResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllOrdersQueryHandler constructor.
     *
     * @param Order                        $api
     * @param OrderResponseParserInterface $responseParser
     */
    public function __construct(
        Order $api,
        OrderResponseParserInterface $responseParser
    ) {
        $this->api = $api;
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchChangedOrdersQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $lastCangedTime = $this->getChangedDateTime();
        $currentDateTime = $this->getCurrentDateTime();

        $criteria = [
            'createdAtFrom' => $lastCangedTime->format(DATE_W3C),
            'createdAtTo' => $currentDateTime->format(DATE_W3C),
        ];

        $orders = $this->api->findBy($criteria);

        foreach ($orders as $element) {
            $result = $this->responseParser->parse($element);

            if (empty($result)) {
                continue;
            }

            $parsedElements = array_filter($result);

            foreach ($parsedElements as $parsedElement) {
                yield $parsedElement;
            }
        }

        $this->setChangedDateTime($currentDateTime);
    }
}
