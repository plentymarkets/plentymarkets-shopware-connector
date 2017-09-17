<?php

namespace PlentyConnector\Connector\Validator\Product\Stock;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Product\Stock\Stock;
use PlentyConnector\Connector\Validator\ValidatorInterface;

/**
 * Class StockValidator
 */
class StockValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof Stock;
    }

    /**
     * @param Stock $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getIdentifier(), null, 'product.variation.stock.identifier');
        Assertion::uuid($object->getVariationIdentifier(), null, 'product.variation.stock.variationIdentifier');
        Assertion::float($object->getStock(), null, 'product.variation.stock.stock');
    }
}
