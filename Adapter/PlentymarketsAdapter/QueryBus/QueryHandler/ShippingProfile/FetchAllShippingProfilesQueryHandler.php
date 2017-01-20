<?php

namespace PlentymarketsAdapter\QueryBus\QueryHandler\ShippingProfile;

use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\Query\ShippingProfile\FetchAllShippingProfilesQuery;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\ShippingProfile\ShippingProfileResponseParserInterface;

/**
 * Class FetchAllShippingProfilesQueryHandler
 */
class FetchAllShippingProfilesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var ShippingProfileResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllShippingProfilesQueryHandler constructor.
     *
     * @param ClientInterface $client
     * @param ShippingProfileResponseParserInterface $responseParser
     */
    public function __construct(
        ClientInterface $client,
        ShippingProfileResponseParserInterface $responseParser
    ) {
        $this->client = $client;
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllShippingProfilesQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $shippingProfiles = array_map(function ($shippingProfile) {
            return $this->responseParser->parse($shippingProfile);
        }, $this->client->request('GET', 'orders/shipping/presets'));

        return array_filter($shippingProfiles);
    }
}
