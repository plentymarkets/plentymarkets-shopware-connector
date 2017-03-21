<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Product;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Query\FetchQueryInterface;
use PlentyConnector\Connector\ServiceBus\Query\Product\FetchProductQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\TransferObject\Product\Product;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ReadApi\Item;
use PlentymarketsAdapter\ResponseParser\Product\ProductResponseParserInterface;

/**
 * Class FetchProductQueryHandler.
 */
class FetchProductQueryHandler implements QueryHandlerInterface
{
    /**
     * @var Item
     */
    private $itemApi;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var ProductResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchProductQueryHandler constructor.
     *
     * @param Item $itemApi
     * @param IdentityServiceInterface $identityService
     * @param ProductResponseParserInterface $responseParser
     */
    public function __construct(
        Item $itemApi,
        IdentityServiceInterface $identityService,
        ProductResponseParserInterface $responseParser
    ) {
        $this->identityService = $identityService;
        $this->responseParser = $responseParser;
        $this->itemApi = $itemApi;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchProductQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        /**
         * @var FetchQueryInterface $query
         */
        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $query->getIdentifier(),
            'objectType' => Product::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        $product = $this->itemApi->findOne($identity->getAdapterIdentifier());

        $result = [];

        $result[] = $this->responseParser->parse($product, $result);

        return array_filter($result);
    }
}
