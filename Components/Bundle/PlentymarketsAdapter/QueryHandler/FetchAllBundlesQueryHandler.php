<?php

namespace PlentyConnector\Components\Bundle\PlentymarketsAdapter\QueryHandler;

use PlentyConnector\Components\Bundle\PlentymarketsAdapter\ResponseParser\BundleResponseParser;
use PlentyConnector\Components\Bundle\Query\FetchAllBundlesQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ReadApi\Item;

/**
 * Class FetchAllBundlesQueryHandler.
 */
class FetchAllBundlesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var Item
     */
    private $itemApi;

    /**
     * @var BundleResponseParser
     */
    private $responseParser;

    /**
     * FetchAllBundlesQueryHandler constructor.
     *
     * @param Item                 $itemApi
     * @param BundleResponseParser $responseParser
     */
    public function __construct(Item $itemApi, BundleResponseParser $responseParser)
    {
        $this->itemApi = $itemApi;
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllBundlesQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $products = $this->itemApi->findAll();

        foreach ($products as $element) {
            $result = $this->responseParser->parse($element);

            if (empty($result)) {
                continue;
            }

            $parsedElements = array_filter($result);

            foreach ($parsedElements as $parsedElement) {
                yield $parsedElement;
            }
        }
    }
}
