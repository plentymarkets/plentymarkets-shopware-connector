<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\MediaCategory;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Query\FetchTransferObjectQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\TransferObject\MediaCategory\MediaCategory;
use PlentymarketsAdapter\Helper\MediaCategoryHelperInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\MediaCategory\MediaCategoryResponseParserInterface;

/**
 * Class FetchMediaCategoryHandler
 */
class FetchMediaCategoryHandler implements QueryHandlerInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var MediaCategoryHelperInterface
     */
    private $mediaCategoryHelper;

    /**
     * @var MediaCategoryResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchMediaCategoryHandler constructor.
     *
     * @param IdentityServiceInterface             $identityService
     * @param MediaCategoryHelperInterface         $mediaCategoryHelper
     * @param MediaCategoryResponseParserInterface $responseParser
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        MediaCategoryHelperInterface $mediaCategoryHelper,
        MediaCategoryResponseParserInterface $responseParser
    ) {
        $this->identityService = $identityService;
        $this->mediaCategoryHelper = $mediaCategoryHelper;
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchTransferObjectQuery &&
            PlentymarketsAdapter::NAME === $query->getAdapterName() &&
            MediaCategory::TYPE === $query->getObjectType() &&
            QueryType::ONE === $query->getQueryType();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        /**
         * @var FetchTransferObjectQuery $query
         */
        $result = [];

        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $query->getObjectIdentifier(),
            'objectType' => MediaCategory::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $identity) {
            return [];
        }

        $caegories = $this->mediaCategoryHelper->getCategories();

        if (array_key_exists($identity->getAdapterIdentifier(), $caegories)) {
            $result[] = $this->responseParser->parse($caegories[$identity->getAdapterIdentifier()]);
        }

        return array_filter($result);
    }
}
