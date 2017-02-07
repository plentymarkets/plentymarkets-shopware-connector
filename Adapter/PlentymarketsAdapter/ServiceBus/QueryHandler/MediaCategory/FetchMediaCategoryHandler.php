<?php

namespace PlentymarketsAdapter\ServiceBus\QueryHandler\MediaCategory;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Query\FetchQueryInterface;
use PlentyConnector\Connector\ServiceBus\Query\Manufacturer\FetchManufacturerQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\TransferObject\MediaCategory\MediaCategory;
use PlentymarketsAdapter\Helper\MediaCategoryHelper;
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
     * @var MediaCategoryHelper
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
     * @param MediaCategoryHelper                  $mediaCategoryHelper
     * @param MediaCategoryResponseParserInterface $responseParser
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        MediaCategoryHelper $mediaCategoryHelper,
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
        return $query instanceof FetchManufacturerQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        /**
         * @var FetchQueryInterface $query
         */
        $result = [];

        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $query->getIdentifier(),
            'objectType' => MediaCategory::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        $caegories = $this->mediaCategoryHelper->getCategories();

        if (array_key_exists($identity->getAdapterIdentifier(), $caegories)) {
            $result[] = $this->responseParser->parse($caegories[$identity->getAdapterIdentifier()]);
        }

        return array_filter($result);
    }
}
