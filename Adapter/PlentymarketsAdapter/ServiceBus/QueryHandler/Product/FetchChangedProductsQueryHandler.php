<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Product;

use PlentyConnector\Connector\ServiceBus\Query\Product\FetchChangedProductsQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\Helper\LanguageHelper;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Product\ProductResponseParserInterface;
use PlentymarketsAdapter\ServiceBus\ChangedDateTimeTrait;

/**
 * Class FetchChangedProductsQueryHandler.
 */
class FetchChangedProductsQueryHandler implements QueryHandlerInterface
{
    use ChangedDateTimeTrait;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var LanguageHelper
     */
    private $languageHelper;

    /**
     * @var ProductResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchChangedProductsQueryHandler constructor.
     *
     * @param ClientInterface $client
     * @param LanguageHelper $languageHelper
     * @param ProductResponseParserInterface $responseParser
     */
    public function __construct(
        ClientInterface $client,
        LanguageHelper $languageHelper,
        ProductResponseParserInterface $responseParser
    ) {
        $this->client = $client;
        $this->languageHelper = $languageHelper;
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchChangedProductsQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $lastCangedTime = $this->getChangedDateTime();

        $currentDateTime = $this->getCurrentDateTime();
        $oldTimestamp = $lastCangedTime->format(DATE_W3C);
        $newTimestamp = $currentDateTime->format(DATE_W3C);

        $products = $this->client->request('GET', 'items', [
            'lang' => $this->languageHelper->getLanguagesQueryString(),
            'updatedBetween' =>  $oldTimestamp . ',' . $newTimestamp,
        ]);

        $result = [];

        foreach ($products as $product) {
            $result[] = $this->responseParser->parse($product, $result);
        }

        if (!empty($result)) {
            $this->setChangedDateTime($currentDateTime);
        }

        return $result;
    }
}
