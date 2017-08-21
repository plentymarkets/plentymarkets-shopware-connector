<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Country;

use PlentyConnector\Connector\ServiceBus\Query\Country\FetchAllCountriesQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Country\CountryResponseParserInterface;

/**
 * Class FetchAllCountriesQueryHandler
 */
class FetchAllCountriesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var CountryResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllCountriesQueryHandler constructor.
     *
     * @param ClientInterface                $client
     * @param CountryResponseParserInterface $responseParser
     */
    public function __construct(
        ClientInterface $client,
        CountryResponseParserInterface $responseParser
    ) {
        $this->client = $client;
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllCountriesQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $countries = $this->client->getIterator('orders/shipping/countries');

        $result = [];
        foreach ($countries as $country) {
            $result[] = $this->responseParser->parse($country);
        }

        return array_filter($result);
    }
}
