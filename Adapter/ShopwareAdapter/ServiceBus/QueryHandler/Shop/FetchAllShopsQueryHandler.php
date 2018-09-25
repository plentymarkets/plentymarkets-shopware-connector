<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\Shop;

use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Connector\ServiceBus\Query\FetchTransferObjectQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use Shopware\Models\Shop\Repository;
use Shopware\Models\Shop\Shop as ShopModel;
use ShopwareAdapter\ResponseParser\Shop\ShopResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

class FetchAllShopsQueryHandler implements QueryHandlerInterface
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var ShopResponseParserInterface
     */
    private $responseParser;

    public function __construct(
        EntityManagerInterface $entityManager,
        ShopResponseParserInterface $responseParser
    ) {
        $this->repository = $entityManager->getRepository(ShopModel::class);
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchTransferObjectQuery &&
            $query->getAdapterName() === ShopwareAdapter::NAME &&
            $query->getObjectType() === Shop::TYPE &&
            $query->getQueryType() === QueryType::ALL;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $elements = $this->repository->getListQuery(['active' => true], ['id' => 'ASC'])->getArrayResult();

        foreach ($elements as $element) {
            $result = $this->responseParser->parse($element);

            if (null === $result) {
                continue;
            }

            yield $result;
        }
    }
}
