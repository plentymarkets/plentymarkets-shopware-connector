<?php

namespace SystemConnector\Validator\Product\Stock;

use Assert\Assertion;
use SystemConnector\TransferObject\Product\Stock\Stock;
use SystemConnector\Validator\ValidatorInterface;

class StockValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object): bool
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
