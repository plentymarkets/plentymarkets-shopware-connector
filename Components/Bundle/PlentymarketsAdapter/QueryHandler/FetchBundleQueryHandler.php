<?php

namespace PlentyConnector\Components\Bundle\PlentymarketsAdapter\QueryHandler;

use PlentyConnector\Components\Bundle\PlentymarketsAdapter\ResponseParser\BundleResponseParserInterface;
use PlentyConnector\Components\Bundle\TransferObject\Bundle;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ReadApi\Item;
use PlentymarketsAdapter\ReadApi\Item\Variation;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\ServiceBus\Query\FetchTransferObjectQuery;
use SystemConnector\ServiceBus\Query\QueryInterface;
use SystemConnector\ServiceBus\QueryHandler\QueryHandlerInterface;
use SystemConnector\ServiceBus\QueryType;

class FetchBundleQueryHandler implements QueryHandlerInterface
{
    /**
     * @var Item
     */
    private $itemApi;

    /**
     * @var Variation
     */
    private $variationApi;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var BundleResponseParserInterface
     */
    private $responseParser;

    public function __construct(
        Item $itemApi,
        Variation $variationApi,
        IdentityServiceInterface $identityService,
        BundleResponseParserInterface $responseParser
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
            $query->getObjectType() === Bundle::TYPE &&
            $query->getQueryType() === QueryType::ALL;
    }

    /**
     * {@inheritdoc}
     *
     * @var FetchTransferObjectQuery $query
     */
    public function handle(QueryInterface $query): array
    {
        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $query->getObjectIdentifier(),
            'objectType' => Bundle::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $identity) {
            return [];
        }

        $variation = $this->variationApi->findBy(['id' => $identity->getAdapterIdentifier()]);

        if (empty($variation)) {
            return [];
        }

        $variation = array_shift($variation);

        $product = $this->itemApi->findOne($variation['itemId']);

        $result = $this->responseParser->parse($product);

        return array_filter($result);
    }
}
