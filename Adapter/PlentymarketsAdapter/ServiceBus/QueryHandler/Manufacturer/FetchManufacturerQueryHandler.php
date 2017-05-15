<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Manufacturer;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Query\FetchQueryInterface;
use PlentyConnector\Connector\ServiceBus\Query\Manufacturer\FetchManufacturerQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Manufacturer\ManufacturerResponseParserInterface;

/**
 * Class FetchManufacturerQueryHandler
 */
class FetchManufacturerQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var ManufacturerResponseParserInterface
     */
    private $manufacturerResponseParser;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * FetchManufacturerQueryHandler constructor.
     *
     * @param ClientInterface                     $client
     * @param ManufacturerResponseParserInterface $manufacturerResponseParser
     * @param IdentityServiceInterface            $identityService
     */
    public function __construct(
        ClientInterface $client,
        ManufacturerResponseParserInterface $manufacturerResponseParser,
        IdentityServiceInterface $identityService
    ) {
        $this->client = $client;
        $this->manufacturerResponseParser = $manufacturerResponseParser;
        $this->identityService = $identityService;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchManufacturerQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        /**
         * @var FetchQueryInterface $query
         */
        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $query->getIdentifier(),
            'objectType' => Manufacturer::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $identity) {
            return [];
        }

        $element = $this->client->request('GET', 'items/manufacturers/' . $identity->getAdapterIdentifier());

        $result = $this->manufacturerResponseParser->parse($element);

        return array_filter($result);
    }
}
