<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Product;

use PlentyConnector\Connector\ServiceBus\Query\Product\FetchChangedProductsQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ReadApi\Item;
use PlentymarketsAdapter\ResponseParser\Product\ProductResponseParserInterface;
use PlentymarketsAdapter\ServiceBus\ChangedDateTimeTrait;

/**
 * Class FetchChangedProductsQueryHandler.
 */
class FetchChangedProductsQueryHandler implements QueryHandlerInterface
{
    use ChangedDateTimeTrait;

    /**
     * @var ProductResponseParserInterface
     */
    private $responseParser;

    /**
     * @var Item
     */
    private $itemApi;

    /**
     * FetchChangedProductsQueryHandler constructor.
     *
     * @param Item $itemApi
     * @param ProductResponseParserInterface $responseParser
     */
    public function __construct(
        Item $itemApi,
        ProductResponseParserInterface $responseParser
    ) {
        $this->itemApi = $itemApi;
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchChangedProductsQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $lastCangedTime = $this->getChangedDateTime();

        $currentDateTime = $this->getCurrentDateTime();
        $oldTimestamp = $lastCangedTime->format(DATE_W3C);
        $newTimestamp = $currentDateTime->format(DATE_W3C);

        $products = $this->itemApi->findChanged($oldTimestamp, $newTimestamp);

        $result = [];

        foreach ($products as $product) {
            $result[] = $this->responseParser->parse($product, $result);
        }

        if (!empty($result)) {
            $this->setChangedDateTime($currentDateTime);
        }

        return array_filter($result);
    }
}
