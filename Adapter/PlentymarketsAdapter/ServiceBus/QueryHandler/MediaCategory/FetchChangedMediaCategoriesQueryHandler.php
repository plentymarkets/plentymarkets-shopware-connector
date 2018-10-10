<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\MediaCategory;

use DateTime;
use Exception;
use PlentymarketsAdapter\Helper\MediaCategoryHelperInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\MediaCategory\MediaCategoryResponseParserInterface;
use Psr\Log\LoggerInterface;
use SystemConnector\ConfigService\ConfigServiceInterface;
use SystemConnector\Console\OutputHandler\OutputHandlerInterface;
use SystemConnector\ServiceBus\Query\FetchTransferObjectQuery;
use SystemConnector\ServiceBus\Query\QueryInterface;
use SystemConnector\ServiceBus\QueryHandler\QueryHandlerInterface;
use SystemConnector\ServiceBus\QueryType;
use SystemConnector\TransferObject\MediaCategory\MediaCategory;

class FetchChangedMediaCategoriesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ConfigServiceInterface
     */
    private $configService;

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

    public function __construct(
        ConfigServiceInterface $configService,
        MediaCategoryHelperInterface $mediaCategoryHelper,
        MediaCategoryResponseParserInterface $responseParser,
        LoggerInterface $logger,
        OutputHandlerInterface $outputHandler
    ) {
        $this->configService = $configService;
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
        $synced = $this->configService->get('PlentymarketsAdapter.MediaCategoriesSynched');

        if (null === $synced) {
            $elements = $this->mediaCategoryHelper->getCategories();

            $this->configService->set('PlentymarketsAdapter.MediaCategoriesSynched', new DateTime());
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
