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
     * @param Item                           $itemApi
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

        $products = $this->itemApi->findChanged($lastCangedTime, $currentDateTime);

        foreach ($products as $element) {
            $parsedElements = array_filter($this->responseParser->parse($element));

            foreach ($parsedElements as $parsedElement) {
                yield $parsedElement;
            }
        }

        $this->setChangedDateTime($currentDateTime);
    }
}
