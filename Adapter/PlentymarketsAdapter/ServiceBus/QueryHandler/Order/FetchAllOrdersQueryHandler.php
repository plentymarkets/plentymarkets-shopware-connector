<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\Order;

use PlentyConnector\Connector\ServiceBus\Query\Order\FetchAllOrdersQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Console\OutputHandler\OutputHandlerInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ReadApi\Order\Order;
use PlentymarketsAdapter\ResponseParser\Order\OrderResponseParserInterface;
use Psr\Log\LoggerInterface;

/**
 * Class FetchAllOrdersQueryHandler
 */
class FetchAllOrdersQueryHandler implements QueryHandlerInterface
{
    /**
     * @var Order
     */
    private $api;

    /**
     * @var OrderResponseParserInterface
     */
    private $responseParser;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OutputHandlerInterface
     */
    private $outputHandler;

    /**
     * FetchAllOrdersQueryHandler constructor.
     *
     * @param Order                        $api
     * @param OrderResponseParserInterface $responseParser
     * @param LoggerInterface              $logger
     * @param OutputHandlerInterface       $outputHandler
     */
    public function __construct(
        Order $api,
        OrderResponseParserInterface $responseParser,
        LoggerInterface $logger,
        OutputHandlerInterface $outputHandler
    ) {
        $this->api = $api;
        $this->responseParser = $responseParser;
        $this->logger = $logger;
        $this->outputHandler = $outputHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllOrdersQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $elements = $this->api->findAll();

        $this->outputHandler->startProgressBar(count($elements));

        $parsedElements = [];
        foreach ($elements as $element) {
            try {
                $result = $this->responseParser->parse($element);
            } catch (Exception $exception) {
                $this->logger->error($exception->getMessage());

                $result = null;
            }

            $this->outputHandler->advanceProgressBar();

            if (empty($result)) {
                continue;
            }

            $parsedElements = array_filter($result);

            foreach ($parsedElements as $parsedElement) {
                $parsedElements[] = $parsedElement;
            }
        }

        $this->outputHandler->finishProgressBar();

        return $parsedElements;
    }
}
