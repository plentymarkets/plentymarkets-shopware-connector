<?php

namespace PlentymarketsAdapter\QueryBus\Handler\Manufacturer;

use Exception;
use PlentyConnector\Connector\Config\ConfigServiceInterface;
use PlentyConnector\Connector\QueryBus\Handler\QueryHandlerInterface;
use PlentyConnector\Connector\QueryBus\Query\Manufacturer\GetChangedManufacturerQuery;
use PlentyConnector\Connector\TransferObject\Manufacturer\ManufacturerInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\QueryBus\ChangedDateTimeTrait;
use PlentymarketsAdapter\ResponseParser\ResponseParserInterface;
use Psr\Log\LoggerInterface;

/**
 * Class GetChangedRemoteManufacturerQueryHandler.
 */
class GetChangedManufacturerQueryHandler implements QueryHandlerInterface
{
    use ChangedDateTimeTrait;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var ConfigServiceInterface
     */
    private $config;

    /**
     * @var ResponseParserInterface
     */
    private $responseMapper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * GetChangedManufacturerQueryHandler constructor.
     *
     * @param ClientInterface         $client
     * @param ConfigServiceInterface  $config
     * @param ResponseParserInterface $responseMapper
     * @param LoggerInterface         $logger
     */
    public function __construct(
        ClientInterface $client,
        ConfigServiceInterface $config,
        ResponseParserInterface $responseMapper,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->config = $config;
        $this->responseMapper = $responseMapper;
        $this->logger = $logger;
    }

    /**
     * @param GetChangedManufacturerQuery $event
     *
     * @return bool
     */
    public function supports($event)
    {
        return
            $event instanceof GetChangedManufacturerQuery &&
            $event->getAdapterName() === PlentymarketsAdapter::getName()
        ;
    }

    /**
     * @param GetRemoteManufacturerQuery $event
     *
     * @return ManufacturerInterface[]
     *
     * @throws \UnexpectedValueException
     */
    public function handle($event)
    {
        $criteria = [
            'lastUpdateTimestamp' => $this->getChangedDateTime($this->config),
        ];

        $result = [];
        foreach ($this->client->getIterator('items/manufacturers', $criteria) as $element) {
            try {
                $result[] = $this->responseMapper->parseManufacturer($element);
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }

        if ([] !== $result) {
            $this->setChangedDateTime($this->config);
        }

        return $result;
    }
}
