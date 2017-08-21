<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Currency;

use PlentyConnector\Connector\ServiceBus\Query\Currency\FetchAllCurrenciesQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Currency\CurrencyResponseParserInterface;

/**
 * Class FetchAllCurrenciesQueryHandler
 */
class FetchAllCurrenciesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var CurrencyResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllCurrenciesQueryHandler constructor.
     *
     * @param ClientInterface                 $client
     * @param CurrencyResponseParserInterface $responseParser
     */
    public function __construct(
        ClientInterface $client,
        CurrencyResponseParserInterface $responseParser
    ) {
        $this->client = $client;
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllCurrenciesQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $currencies = $this->client->getIterator('orders/currencies');

        $result = [];
        foreach ($currencies as $currency) {
            $result[] = $this->responseParser->parse($currency);
        }

        return array_filter($result);
    }
}
