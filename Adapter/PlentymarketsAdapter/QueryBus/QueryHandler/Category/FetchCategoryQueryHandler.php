<?php

namespace PlentymarketsAdapter\QueryBus\QueryHandler\Category;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\QueryBus\Query\Category\FetchCategoryQuery;
use PlentyConnector\Connector\QueryBus\Query\FetchQueryInterface;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\TransferObject\Category\Category;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Category\CategoryResponseParserInterface;
use PlentymarketsAdapter\ResponseParser\Media\MediaResponseParserInterface;

/**
 * Class FetchCategoryQueryHandler
 */
class FetchCategoryQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var CategoryResponseParserInterface
     */
    private $categoryResponseParser;

    /**
     * @var MediaResponseParserInterface
     */
    private $mediaResponseParser;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * FetchCategoryQueryHandler constructor.
     *
     * @param ClientInterface $client
     * @param CategoryResponseParserInterface $categoryResponseParser
     * @param MediaResponseParserInterface $mediaResponseParser
     * @param IdentityServiceInterface $identityService
     */
    public function __construct(
        ClientInterface $client,
        CategoryResponseParserInterface $categoryResponseParser,
        MediaResponseParserInterface $mediaResponseParser,
        IdentityServiceInterface $identityService
    ) {
        $this->client = $client;
        $this->categoryResponseParser = $categoryResponseParser;
        $this->mediaResponseParser = $mediaResponseParser;
        $this->identityService = $identityService;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchCategoryQuery &&
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
        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $query->getIdentifier(),
            'objectType' => Category::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        $element = $this->client->request('GET', 'categories/' . $identity->getAdapterIdentifier(), [
            'with' => 'clients,details',
        ]);

        if (!empty($element['image'])) {
            $result[] = $media = $this->mediaResponseParser->parse([
                'link' => $element['image'],
                'name' => $element['name']
            ]);

            $element['imageIdentifier'] = $media->getIdentifier();
        }

        $result[] = $this->categoryResponseParser->parse($element);

        return array_filter($result);
    }
}
