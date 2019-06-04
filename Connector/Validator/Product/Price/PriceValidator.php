<?php

namespace SystemConnector\Validator\Product\Price;

use Assert\Assertion;
use SystemConnector\TransferObject\Product\Price\Price;
use SystemConnector\Validator\ValidatorInterface;

class PriceValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object): bool
    {
        return $object instanceof Price;
    }

    /**
     * @param Price $object
     */
    public function validate($object)
    {
        Assertion::float($object->getPrice(), null, 'product.price.price');
        Assertion::greaterOrEqualThan($object->getPrice(), 0.0, null, 'product.price.price');
        Assertion::float($object->getPseudoPrice(), null, 'product.price.pseudoPrice');
        Assertion::greaterOrEqualThan($object->getPseudoPrice(), 0.0, null, 'product.price.pseudoPrice');
        Assertion::nullOrUuid($object->getCustomerGroupIdentifier(), null, 'product.price.customerGroupIdentifier');
        Assertion::float($object->getFromAmount(), null, 'product.price.fromAmount');
        Assertion::greaterThan($object->getFromAmount(), 0.0, null, 'product.price.fromAmount');
        Assertion::nullOrFloat($object->getToAmount(), null, 'product.price.toAmount');
    }
}
