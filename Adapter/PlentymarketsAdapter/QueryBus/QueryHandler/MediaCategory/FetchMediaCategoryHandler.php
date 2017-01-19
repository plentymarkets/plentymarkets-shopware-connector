<?php

namespace PlentymarketsAdapter\QueryBus\QueryHandler\MediaCategory;

use Exception;
use PlentyConnector\Adapter\PlentymarketsAdapter\Client\Exception\InvalidResponseException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\QueryBus\Query\FetchQueryInterface;
use PlentyConnector\Connector\QueryBus\Query\Manufacturer\FetchManufacturerQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;
use PlentyConnector\Connector\TransferObject\MediaCategory\MediaCategory;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\Client\Exception\InvalidCredentialsException;
use PlentymarketsAdapter\Helper\MediaCategoryHelper;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\ResponseParserInterface;
use Psr\Log\LoggerInterface;
use ShopwareAdapter\ResponseParser\ResponseParser;
use UnexpectedValueException;

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
     * @var ResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchMediaCategoryHandler constructor.
     *
     * @param IdentityServiceInterface $identityService
     * @param MediaCategoryHelper $mediaCategoryHelper
     * @param ResponseParserInterface $responseParser
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        MediaCategoryHelper $mediaCategoryHelper,
        ResponseParserInterface $responseParser
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
            $result[] =  $this->responseParser->parse($caegories[$identity->getAdapterIdentifier()]);
        }

        return array_filter($result);
    }
}
