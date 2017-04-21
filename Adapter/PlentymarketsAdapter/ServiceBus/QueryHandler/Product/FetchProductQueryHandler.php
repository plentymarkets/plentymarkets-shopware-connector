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
     * @param Item                           $itemApi
     * @param IdentityServiceInterface       $identityService
     * @param ProductResponseParserInterface $responseParser
     */
    public function __construct(
        Item $itemApi,
        IdentityServiceInterface $identityService,
        ProductResponseParserInterface $responseParser
    ) {
        $this->itemApi = $itemApi;
        $this->identityService = $identityService;
        $this->responseParser = $responseParser;
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

        if (null === $identity) {
            return [];
        }

        $product = $this->itemApi->findOne($identity->getAdapterIdentifier());

        $result = $this->responseParser->parse($product);

        return array_filter($result);
    }
}
