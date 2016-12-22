<?php

namespace ShopwareAdapter\QueryBus\QueryHandler\ShippingProfile;

use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\Query\ShippingProfile\FetchAllShippingProfilesQuery;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use Shopware\Models\Dispatch\Dispatch;
use Shopware\Models\Dispatch\Repository;
use ShopwareAdapter\ResponseParser\ResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchAllShippingProfilesHandler
 */
class FetchAllShippingProfilesHandler implements QueryHandlerInterface
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var ResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllShippingProfilesHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ResponseParserInterface $responseParser
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ResponseParserInterface $responseParser
    ) {
        $this->repository = $entityManager->getRepository(Dispatch::class);
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $event)
    {
        return $event instanceof FetchAllShippingProfilesQuery &&
            $event->getAdapterName() === ShopwareAdapter::getName();
    }

    /**
     * TODO: Refaktor the foreach loop
     *
     * {@inheritdoc}
     */
    public function handle(QueryInterface $event)
    {
        $query = $this->repository->getListQuery();

        $shippingProfiles = array_map(function ($shippingProfile) {
            return $this->responseParser->parse($shippingProfile);
        }, $query->getArrayResult());

        return array_filter($shippingProfiles);
    }
}
