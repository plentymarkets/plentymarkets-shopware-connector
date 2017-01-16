<?php

namespace ShopwareAdapter\QueryBus\QueryHandler\Shop;

use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\Query\Shop\FetchAllShopsQuery;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use Shopware\Models\Dispatch\Repository;
use Shopware\Models\Shop\Shop;
use ShopwareAdapter\ResponseParser\ResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchAllShopsQueryHandler
 */
class FetchAllShopsQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ResponseParserInterface
     */
    private $responseParser;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * FetchAllShopsQueryHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ResponseParserInterface $responseParser
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ResponseParserInterface $responseParser
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
