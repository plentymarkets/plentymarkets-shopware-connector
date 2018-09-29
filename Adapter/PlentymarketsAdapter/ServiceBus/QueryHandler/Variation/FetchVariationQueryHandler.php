<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Variation;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Query\FetchTransferObjectQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\TransferObject\Product\Variation\Variation;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ReadApi\Item as ItemApi;
use PlentymarketsAdapter\ReadApi\Item\Variation as VariationApi;
use PlentymarketsAdapter\ResponseParser\Product\Variation\VariationResponseParserInterface;

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
     * @var VariationResponseParserInterface
     */
    private $responseParser;

    public function __construct(
        ItemApi $itemApi,
        VariationApi $variationApi,
        IdentityServiceInterface $identityService,
        VariationResponseParserInterface $responseParser
    ) {
        $this->itemApi = $itemApi;
        $this->variationApi = $variationApi;
        $this->identityService = $identityService;
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
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
