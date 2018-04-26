<?php

namespace PlentyConnector\Components\Bundle\ShopwareAdapter\ResponseParser\Order;

use Exception;
use ShopwareAdapter\ResponseParser\Order\OrderResponseParserInterface;
use Doctrine\DBAL\Connection;


/**
 * Class OrderResponseParser
 */
class OrderResponseParser implements OrderResponseParserInterface
{
    /**
     * @var OrderResponseParserInterface
     */
    private $parentOrderResponseParser;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * OrderResponseParser constructor.
     * @param OrderResponseParserInterface $parentOrderResponseParser
     * @param Connection $connection
     */
    public function __construct(
        OrderResponseParserInterface $parentOrderResponseParser,
        Connection $connection
    ) {
        $this->parentOrderResponseParser = $parentOrderResponseParser;
        $this->connection = $connection;
    }


    /**
     * {@inheritdoc}
     */
    public function parse(array $entry)
    {
        foreach ($entry['details'] as $key => $item) {

            if (null === $item['attribute']['bundlePackageId']) {
                continue;
            }

            if ($item['mode'] !== 10) {
                unset($entry['details'][$key]);
                continue;
            }
            $entry['details'][$key]['bundle'] = 1;

        }

        $order = $this->parentOrderResponseParser->parse($entry);

        return $order;
    }

}
