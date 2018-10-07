<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\Payment;

use Exception;
use PlentyConnector\Connector\Console\OutputHandler\OutputHandlerInterface;
use PlentyConnector\Connector\ServiceBus\Query\FetchTransferObjectQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\TransferObject\Payment\Payment;
use Psr\Log\LoggerInterface;
use ShopwareAdapter\DataProvider\Order\OrderDataProviderInterface;
use ShopwareAdapter\ResponseParser\Payment\PaymentResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

class FetchChangedPaymentsQueryHandler implements QueryHandlerInterface
{
    /**
     * @var PaymentResponseParserInterface
     */
    private $responseParser;

    /**
     * @var OrderDataProviderInterface
     */
    private $dataProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OutputHandlerInterface
     */
    private $outputHandler;

    public function __construct(
        PaymentResponseParserInterface $responseParser,
        OrderDataProviderInterface $dataProvider,
        LoggerInterface $logger,
        OutputHandlerInterface $outputHandler
    ) {
        $this->responseParser = $responseParser;
        $this->dataProvider = $dataProvider;
        $this->logger = $logger;
        $this->outputHandler = $outputHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchTransferObjectQuery &&
            $query->getAdapterName() === ShopwareAdapter::NAME &&
            $query->getObjectType() === Payment::TYPE &&
            $query->getQueryType() === QueryType::CHANGED;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $elements = $this->dataProvider->getOpenOrders();

        $this->outputHandler->startProgressBar(count($elements));

        foreach ($elements as $element) {
            $element = $this->dataProvider->getOrderDetails($element['id']);

            try {
                $result = $this->responseParser->parse($element);
            } catch (Exception $exception) {
                $this->logger->error($exception->getMessage());

                $result = null;
            }

            if (empty($result)) {
                $result = [];
            }

            $result = array_filter($result);

            foreach ($result as $parsedElement) {
                yield $parsedElement;
            }

            $this->outputHandler->advanceProgressBar();
        }

        $this->outputHandler->finishProgressBar();
    }
}
