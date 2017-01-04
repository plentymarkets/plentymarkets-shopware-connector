<?php

namespace PlentymarketsAdapter\QueryBus\QueryHandler\Manufacturer;

use Exception;
use PlentyConnector\Adapter\PlentymarketsAdapter\Client\Exception\InvalidResponseException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\QueryBus\Query\Manufacturer\FetchManufacturerQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\Client\Exception\InvalidCredentialsException;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\ResponseParserInterface;
use UnexpectedValueException;

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
     * @var ResponseParserInterface
     */
    private $responseMapper;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * FetchManufacturerQueryHandler constructor.
     *
     * @param ClientInterface $client
     * @param ResponseParserInterface $responseMapper
     * @param IdentityServiceInterface $identityService
     */
    public function __construct(
        ClientInterface $client,
        ResponseParserInterface $responseMapper,
        IdentityServiceInterface $identityService
    ) {
        $this->client = $client;
        $this->responseMapper = $responseMapper;
        $this->identityService = $identityService;
    }

    /**
     * @param QueryInterface $event
     *
     * @return bool
     */
    public function supports(QueryInterface $event)
    {
        return $event instanceof FetchManufacturerQuery &&
            $event->getAdapterName() === PlentymarketsAdapter::getName();
    }

    /**
     * @param QueryInterface $event
     *
     * @return TransferObjectInterface
     *
     * @throws InvalidResponseException
     * @throws InvalidCredentialsException
     * @throws Exception
     * @throws UnexpectedValueException
     */
    public function handle(QueryInterface $event)
    {
        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $event->getIdentifier(),
            'objectType' => Manufacturer::getType(),
            'adapterName' => PlentymarketsAdapter::getName(),
        ]);

        $element = $this->client->request('GET', 'items/manufacturers/' . $identity->getAdapterIdentifier());

        return $this->responseMapper->parse($element);
    }
}
