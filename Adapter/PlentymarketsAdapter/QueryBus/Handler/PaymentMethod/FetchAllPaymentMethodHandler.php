<?php

namespace PlentymarketsAdapter\QueryBus\Handler\PaymentMethod;

use PlentyConnector\Connector\QueryBus\Handler\QueryHandlerInterface;
use PlentyConnector\Connector\QueryBus\Query\PaymentMethod\FetchAllPaymentMethodQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use Psr\Log\LoggerInterface;
use ShopwareAdapter\ResponseParser\ResponseParserInterface;

/**
 * Class FetchAllPaymentMethodHandler
 */
class FetchAllPaymentMethodHandler implements QueryHandlerInterface
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * FetchAllPaymentMethodHandler constructor.
     *
     * @param ClientInterface $client
     * @param ResponseParserInterface $responseParser
     * @param LoggerInterface $logger
     */
    public function __construct(
        ClientInterface $client,
        ResponseParserInterface $responseParser,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->responseParser = $responseParser;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $event)
    {
        return
            $event instanceof FetchAllPaymentMethodQuery &&
            $event->getAdapterName() === PlentymarketsAdapter::getName();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $event)
    {
        $paymentMethods = $this->client->request('GET', 'payments/methods');

        $result = [];
        foreach ($paymentMethods as $paymentMethod) {
            try {
                $result[] = $this->responseParser->parse($paymentMethod);
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }

        return $result;
    }
}
