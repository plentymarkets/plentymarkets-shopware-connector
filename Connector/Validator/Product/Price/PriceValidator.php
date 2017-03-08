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
        Assertion::float($object->getPrice());
        Assertion::greaterOrEqualThan($object->getPrice(), 0.0);
        Assertion::float($object->getPseudoPrice());
        Assertion::greaterOrEqualThan($object->getPseudoPrice(), 0.0);
        Assertion::uuid($object->getCustomerGroupIdentifier());
        Assertion::float($object->getFromAmount());
        Assertion::greaterThan($object->getFromAmount(), 0.0);
        Assertion::nullOrFloat($object->getToAmount());
    }
}
