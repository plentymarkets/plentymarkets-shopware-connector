<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\ShippingProfile;

use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\Query\ShippingProfile\FetchAllShippingProfilesQuery;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
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
     * @param ClientInterface                        $client
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
        $shippingProfiles = $this->client->getIterator('orders/shipping/presets');

        $result = [];
        foreach ($shippingProfiles as $shippingProfile) {
            $result[] = $this->responseParser->parse($shippingProfile);
        }

        return array_filter($result);
    }
}
