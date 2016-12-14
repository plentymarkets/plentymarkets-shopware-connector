<?php

namespace ShopwareAdapter\QueryBus\QueryHandler\ShippingProfile;

use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\Query\ShippingProfile\FetchAllShippingProfilesQuery;
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
     * @var ResponseParserInterface
     */
    private $responseParser;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * FetchAllShippingProfilesHandler constructor.
     *
     * @param ResponseParserInterface $responseParser
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        ResponseParserInterface $responseParser,
        EntityManagerInterface $entityManager
    ) {
        $this->responseParser = $responseParser;
        $this->repository = $entityManager->getRepository(Dispatch::class);
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
        $shippingProfiles = $query->getArrayResult();

        $shippingProfiles = array_map(function($shippingProfile) {
            return $this->responseParser->parse($shippingProfile);
        }, $shippingProfiles);

        return array_filter($shippingProfiles);
    }
}
