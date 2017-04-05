<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\ShippingProfile;

use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\Query\ShippingProfile\FetchAllShippingProfilesQuery;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use Shopware\Models\Dispatch\Dispatch;
use Shopware\Models\Dispatch\Repository;
use ShopwareAdapter\ResponseParser\ShippingProfile\ShippingProfileResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchAllShippingProfilesQueryHandler
 */
class FetchAllShippingProfilesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var ShippingProfileResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllShippingProfilesQueryHandler constructor.
     *
     * @param EntityManagerInterface                 $entityManager
     * @param ShippingProfileResponseParserInterface $responseParser
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ShippingProfileResponseParserInterface $responseParser
    ) {
        $this->repository = $entityManager->getRepository(Dispatch::class);
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllShippingProfilesQuery &&
            $query->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $objectQuery = $this->repository->getListQuery();

        $shippingProfiles = array_map(function ($shippingProfile) {
            return $this->responseParser->parse($shippingProfile);
        }, $objectQuery->getArrayResult());

        return array_filter($shippingProfiles);
    }
}
