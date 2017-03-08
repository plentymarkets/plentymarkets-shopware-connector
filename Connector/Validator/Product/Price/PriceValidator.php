<?php

namespace PlentyConnector\Connector\Validator\Product\Price;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Product\Price\Price;
use PlentyConnector\Connector\Validator\ValidatorInterface;

/**
 * Class PriceValidator
 */
class PriceValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof Price;
    }

    /**
     * @param Price $object
     */
    public function validate($object)
    {
        Assertion::float($object->getPrice(), null, 'order.price');
        Assertion::greaterOrEqualThan($object->getPrice(), 0.0, null, 'order.price');
        Assertion::float($object->getPseudoPrice(), null, 'order.pseudoPrice');
        Assertion::greaterOrEqualThan($object->getPseudoPrice(), 0.0, null, 'order.pseudoPrice');
        Assertion::uuid($object->getCustomerGroupIdentifier(), null, 'order.customerGroupIdentifier');
        Assertion::float($object->getFromAmount(), null, 'order.fromAmount');
        Assertion::greaterThan($object->getFromAmount(), 0.0, null, 'order.fromAmount');
        Assertion::nullOrFloat($object->getToAmount(), null, 'order.toAmount');
    }
}
