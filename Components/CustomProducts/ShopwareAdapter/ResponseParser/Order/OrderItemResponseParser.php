<?php

namespace PlentyConnector\Components\CustomProducts\ShopwareAdapter\ResponseParser\Order;

use Shopware\Components\Model\ModelManager;
use ShopwareAdapter\ResponseParser\OrderItem\OrderItemResponseParserInterface;
use SwagCustomProducts\Models\Option;
use SwagCustomProducts\Models\Value;

class OrderItemResponseParser implements OrderItemResponseParserInterface
{
    /**
     * @var OrderItemResponseParserInterface
     */
    private $parentOrderItemResponseParser;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * DecoratedOrderItemResponseParser constructor.
     *
     * @param OrderItemResponseParserInterface $parentOrderItemResponseParser
     * @param ModelManager                     $modelManager
     */
    public function __construct(
        OrderItemResponseParserInterface $parentOrderItemResponseParser,
        ModelManager $modelManager
    ) {
        $this->parentOrderItemResponseParser = $parentOrderItemResponseParser;
        $this->modelManager = $modelManager;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $entry, $taxFree = false)
    {
        if (\ShopwareAdapter\ResponseParser\OrderItem\OrderItemResponseParser::ITEM_TYPE_ID_SURCHARGE === $entry['mode']) {
            if (null !== $this->modelManager->getRepository(Value::class)->findOneBy(['ordernumber' => $entry['articleNumber']]) ||
                null !== $this->modelManager->getRepository(Option::class)->findOneBy(['ordernumber' => $entry['articleNumber']])
            ) {
                $entry['mode'] = 1;
            }
        }

        return $this->parentOrderItemResponseParser->parse($entry, $taxFree);
    }
}
