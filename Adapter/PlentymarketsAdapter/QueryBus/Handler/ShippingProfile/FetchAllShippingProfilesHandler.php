<?php

namespace PlentymarketsAdapter\QueryBus\Handler\ShippingProfile;

use PlentyConnector\Connector\QueryBus\Handler\QueryHandlerInterface;
use PlentyConnector\Connector\QueryBus\Query\PaymentMethod\FetchAllPaymentMethodsQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\ResponseParserInterface;

/**
 * Class FetchAllShippingProfilesHandler
 */
class FetchAllShippingProfilesHandler implements QueryHandlerInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var ResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllShippingProfilesHandler constructor.
     *
     * @param ClientInterface $client
     * @param ResponseParserInterface $responseParser
     */
    public function __construct(
        ClientInterface $client,
        ResponseParserInterface $responseParser
    ) {
        $this->client = $client;
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $event)
    {
        return $event instanceof FetchAllPaymentMethodsQuery &&
            $event->getAdapterName() === PlentymarketsAdapter::getName();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $event)
    {
        $shippingProfiles = $this->client->request('GET', 'payments/methods');

        $shippingProfiles = array_map(function ($shippingProfile) {
            return $this->responseParser->parse($shippingProfile);
        }, $shippingProfiles);

        return array_filter($shippingProfiles);
    }
}
