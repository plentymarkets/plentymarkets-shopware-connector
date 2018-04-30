<?php

namespace PlentyConnector\Components\Bundle\ShopwareAdapter\ResponseParser\Order;

use ShopwareAdapter\ResponseParser\Order\OrderResponseParserInterface;

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
     * OrderResponseParser constructor.
     *
     * @param OrderResponseParserInterface $parentOrderResponseParser
     */
    public function __construct(
        OrderResponseParserInterface $parentOrderResponseParser
    ) {
        $this->parentOrderResponseParser = $parentOrderResponseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $entry)
    {
        foreach ($entry['details'] as $key => $item) {
            if (!isset($item['attribute']['bundlePackageId'])) {
                continue;
            }

            if ($item['mode'] !== 10) {
                unset($entry['details'][$key]);

                continue;
            }

            $entry['details'][$key]['bundle'] = 1;
        }

        return $this->parentOrderResponseParser->parse($entry);
    }
}
