<?php

namespace PlentyConnector\Components\Bundle\PlentymarketsAdapter\QueryHandler;

use PlentyConnector\Components\Bundle\PlentymarketsAdapter\ResponseParser\BundleResponseParserInterface;
use PlentyConnector\Components\Bundle\Query\FetchBundleQuery;
use PlentyConnector\Components\Bundle\TransferObject\Bundle;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Query\FetchQueryInterface;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ReadApi\Item;
use PlentymarketsAdapter\ReadApi\Item\Variation;

/**
 * Class FetchBundleQueryHandler.
 */
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

    /**
     * FetchBundleQueryHandler constructor.
     *
     * @param Item                          $itemApi
     * @param Variation                     $variationApi
     * @param IdentityServiceInterface      $identityService
     * @param BundleResponseParserInterface $responseParser
     */
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
        return $query instanceof FetchBundleQuery &&
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
