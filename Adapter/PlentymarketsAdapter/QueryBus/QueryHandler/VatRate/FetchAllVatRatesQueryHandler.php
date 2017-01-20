<?php

namespace PlentymarketsAdapter\QueryBus\QueryHandler\VatRate;

use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\Query\VatRate\FetchAllVatRatesQuery;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\VatRate\VatRateResponseParserInterface;

/**
 * Class FetchAllVatRatesQueryHandler
 */
class FetchAllVatRatesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var VatRateResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllVatRatesQueryHandler constructor.
     *
     * @param ClientInterface $client
     * @param VatRateResponseParserInterface $responseParser
     */
    public function __construct(
        ClientInterface $client,
        VatRateResponseParserInterface $responseParser
    ) {
        $this->client = $client;
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllVatRatesQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $vatRates = [];
        $vatRatesByCountry = $this->client->request('GET', 'vat');

        foreach ($vatRatesByCountry as $countryVat) {
            foreach ($countryVat['vatRates'] as $rate) {
                $vatRates[$rate['id']] = $rate;
            }
        }

        $vatRates = array_map(function ($vatRate) {
            return $this->responseParser->parse($vatRate);
        }, $vatRates);

        return array_filter($vatRates);
    }
}
