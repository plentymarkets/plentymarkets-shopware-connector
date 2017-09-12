<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\MediaCategory;

use PlentyConnector\Connector\ConfigService\ConfigServiceInterface;
use PlentyConnector\Connector\ServiceBus\Query\MediaCategory\FetchChangedMediaCategoriesQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Console\OutputHandler\OutputHandlerInterface;
use PlentymarketsAdapter\Helper\MediaCategoryHelper;
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
     * @var MediaCategoryHelper
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
     * @param MediaCategoryHelper                  $mediaCategoryHelper
     * @param MediaCategoryResponseParserInterface $responseParser
     * @param LoggerInterface                      $logger
     * @param OutputHandlerInterface               $outputHandler
     */
    public function __construct(
        ConfigServiceInterface $config,
        MediaCategoryHelper $mediaCategoryHelper,
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
        return $query instanceof FetchChangedMediaCategoriesQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $synced = $this->config->get('PlentymarketsAdapter.MediaCategoriesSynched');

        if (null !== $synced) {
            return [];
        }

        $elements = $this->mediaCategoryHelper->getCategories();

        $this->outputHandler->startProgressBar(count($elements));

        $parsedElements = [];
        foreach ($elements as $element) {
            try {
                $parsedElements[] = $this->responseParser->parse($element);
            } catch (Exception $exception) {
                $this->logger->error($exception->getMessage());
            }

            $this->outputHandler->advanceProgressBar();
        }

        $this->outputHandler->finishProgressBar();

        return array_filter($parsedElements);
    }
}
