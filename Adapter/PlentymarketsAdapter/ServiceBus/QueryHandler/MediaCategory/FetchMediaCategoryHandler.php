<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\MediaCategory;

use PlentymarketsAdapter\Helper\MediaCategoryHelperInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\MediaCategory\MediaCategoryResponseParserInterface;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\ServiceBus\Query\FetchTransferObjectQuery;
use SystemConnector\ServiceBus\Query\QueryInterface;
use SystemConnector\ServiceBus\QueryHandler\QueryHandlerInterface;
use SystemConnector\ServiceBus\QueryType;
use SystemConnector\TransferObject\MediaCategory\MediaCategory;

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
            $query->getAdapterName() === PlentymarketsAdapter::NAME &&
            $query->getObjectType() === MediaCategory::TYPE &&
            $query->getQueryType() === QueryType::ONE;
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

        $categories = $this->mediaCategoryHelper->getCategories();

        if (array_key_exists($identity->getAdapterIdentifier(), $categories)) {
            $result[] = $this->responseParser->parse($categories[$identity->getAdapterIdentifier()]);
        }

        return array_filter($result);
    }
}
