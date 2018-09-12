<?php

namespace PlentyConnector\Components\Bundle\PlentymarketsAdapter\QueryHandler;

use PlentyConnector\Components\Bundle\PlentymarketsAdapter\ResponseParser\BundleResponseParserInterface;
use PlentyConnector\Components\Bundle\TransferObject\Bundle;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Query\FetchTransferObjectQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ReadApi\Item;
use PlentymarketsAdapter\ReadApi\Item\Variation;

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
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchTransferObjectQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME &&
            $query->getObjectType() === Bundle::TYPE &&
            $query->getQueryType() === QueryType::ALL;
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
