<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Variation;

use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ReadApi\Item as ItemApi;
use PlentymarketsAdapter\ReadApi\Item\Variation as VariationApi;
use PlentymarketsAdapter\ResponseParser\Product\ProductResponseParserInterface;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\ServiceBus\Query\FetchTransferObjectQuery;
use SystemConnector\ServiceBus\Query\QueryInterface;
use SystemConnector\ServiceBus\QueryHandler\QueryHandlerInterface;
use SystemConnector\ServiceBus\QueryType;
use SystemConnector\TransferObject\Product\Variation\Variation;

class FetchVariationQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ItemApi
     */
    private $itemApi;

    /**
     * @var VariationApi
     */
    private $variationApi;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var ProductResponseParserInterface
     */
    private $responseParser;

    public function __construct(
        ItemApi $itemApi,
        VariationApi $variationApi,
        IdentityServiceInterface $identityService,
        ProductResponseParserInterface $responseParser
    ) {
        $this->itemApi = $itemApi;
        $this->variationApi = $variationApi;
        $this->identityService = $identityService;
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query): bool
    {
        return $query instanceof FetchTransferObjectQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME &&
            $query->getObjectType() === Variation::TYPE &&
            $query->getQueryType() === QueryType::ONE;
    }

    /**
     * {@inheritdoc}
     *
     * @param FetchTransferObjectQuery $query
     */
    public function handle(QueryInterface $query)
    {
        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $query->getObjectIdentifier(),
            'objectType' => Variation::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $identity) {
            return [];
        }

        $variation = $this->variationApi->findBy([
            'id' => $identity->getAdapterIdentifier(),
        ]);

        if (empty($variation)) {
            return [];
        }

        $variation = array_shift($variation);

        $product = $this->itemApi->findOne($variation['itemId']);

        $result = $this->responseParser->parse($product);

        return array_filter($result);
    }
}
