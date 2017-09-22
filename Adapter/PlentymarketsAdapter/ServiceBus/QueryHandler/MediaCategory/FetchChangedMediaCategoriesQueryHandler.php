<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\MediaCategory;

use PlentyConnector\Connector\ConfigService\ConfigServiceInterface;
use PlentyConnector\Connector\ServiceBus\Query\FetchTransferObjectQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\TransferObject\MediaCategory\MediaCategory;
use PlentyConnector\Console\OutputHandler\OutputHandlerInterface;
use PlentymarketsAdapter\Helper\MediaCategoryHelper;
use PlentymarketsAdapter\Helper\MediaCategoryHelperInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\MediaCategory\MediaCategoryResponseParserInterface;
use Psr\Log\LoggerInterface;

/**
 * Class FetchChangedMediaCategoriesQueryHandler.
 */
class FetchChangedMediaCategoriesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ConfigServiceInterface
     */
    private $config;

    /**
     * @var MediaCategoryHelperInterface
     */
    private $mediaCategoryHelper;

    /**
     * @var MediaCategoryResponseParserInterface
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
     * FetchChangedMediaCategoriesQueryHandler constructor.
     *
     * @param ConfigServiceInterface               $config
     * @param MediaCategoryHelperInterface         $mediaCategoryHelper
     * @param MediaCategoryResponseParserInterface $responseParser
     * @param LoggerInterface                      $logger
     * @param OutputHandlerInterface               $outputHandler
     */
    public function __construct(
        ConfigServiceInterface $config,
        MediaCategoryHelperInterface $mediaCategoryHelper,
        MediaCategoryResponseParserInterface $responseParser,
        LoggerInterface $logger,
        OutputHandlerInterface $outputHandler
    ) {
        $this->config = $config;
        $this->mediaCategoryHelper = $mediaCategoryHelper;
        $this->responseParser = $responseParser;
        $this->logger = $logger;
        $this->outputHandler = $outputHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchTransferObjectQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME &&
            $query->getObjectType() === MediaCategory::TYPE &&
            $query->getQueryType() === QueryType::CHANGED;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $elements = [];
        $synced = $this->config->get('PlentymarketsAdapter.MediaCategoriesSynched');

        if (null === $synced) {
            $elements = $this->mediaCategoryHelper->getCategories();
        }

        $this->outputHandler->startProgressBar(count($elements));

        foreach ($elements as $element) {
            try {
                $result = $this->responseParser->parse($element);
            } catch (Exception $exception) {
                $this->logger->error($exception->getMessage());

                $result = null;
            }

            if (null !== $result) {
                yield $result;
            }

            $this->outputHandler->advanceProgressBar();
        }

        $this->outputHandler->finishProgressBar();
    }
}
