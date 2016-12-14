<?php

namespace ShopwareAdapter\QueryBus\Handler\Shop;

use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Connector\QueryBus\Handler\QueryHandlerInterface;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\Query\Shop\FetchAllShopsQuery;
use Shopware\Models\Dispatch\Repository;
use Shopware\Models\Shop\Shop;
use ShopwareAdapter\ResponseParser\ResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchAllShopsHandler
 */
class FetchAllShopsHandler implements QueryHandlerInterface
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
     * FetchAllShopsHandler constructor.
     *
     * @param ResponseParserInterface $responseParser
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        ResponseParserInterface $responseParser,
        EntityManagerInterface $entityManager
    ) {
        $this->responseParser = $responseParser;
        $this->repository = $entityManager->getRepository(Shop::class);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $event)
    {
        return $event instanceof FetchAllShopsQuery &&
            $event->getAdapterName() === ShopwareAdapter::getName();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $event)
    {
        $query = $this->repository->getListQuery(['active' => true], ['id' => 'ASC']);
        $shops = $query->getArrayResult();

        $shops = array_map(function($shop) {
            return $this->responseParser->parse($shop);
        }, $shops);

        return array_filter($shops);
    }
}
