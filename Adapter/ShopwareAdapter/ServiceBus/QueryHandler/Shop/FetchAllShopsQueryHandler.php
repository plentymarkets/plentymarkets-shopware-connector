<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\Shop;

use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\Query\Shop\FetchAllShopsQuery;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use Shopware\Models\Dispatch\Repository;
use Shopware\Models\Shop\Shop;
use ShopwareAdapter\ResponseParser\Shop\ShopResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchAllShopsQueryHandler
 */
class FetchAllShopsQueryHandler implements QueryHandlerInterface
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var ShopResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllShopsQueryHandler constructor.
     *
     * @param EntityManagerInterface      $entityManager
     * @param ShopResponseParserInterface $responseParser
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ShopResponseParserInterface $responseParser
    ) {
        $this->repository = $entityManager->getRepository(Shop::class);
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllShopsQuery &&
            $query->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $objectQuery = $this->repository->getListQuery(['active' => true], ['id' => 'ASC']);

        $shops = array_map(function ($shop) {
            return $this->responseParser->parse($shop);
        }, $objectQuery->getArrayResult());

        return array_filter($shops);
    }
}
