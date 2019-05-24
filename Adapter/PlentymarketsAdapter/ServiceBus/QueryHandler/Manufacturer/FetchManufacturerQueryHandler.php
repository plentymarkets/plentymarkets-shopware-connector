<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Manufacturer;

use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Manufacturer\ManufacturerResponseParserInterface;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\ServiceBus\Query\FetchTransferObjectQuery;
use SystemConnector\ServiceBus\Query\QueryInterface;
use SystemConnector\ServiceBus\QueryHandler\QueryHandlerInterface;
use SystemConnector\ServiceBus\QueryType;
use SystemConnector\TransferObject\Manufacturer\Manufacturer;

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
    public function supports(QueryInterface $query): bool
    {
        return $query instanceof FetchTransferObjectQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME &&
            $query->getObjectType() === Manufacturer::TYPE &&
            $query->getQueryType() === QueryType::ONE;
    }

    /**
     * {@inheritdoc}
     *
     * @var FetchTransferObjectQuery $query
     */
    public function handle(QueryInterface $query)
    {
        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $query->getObjectIdentifier(),
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
