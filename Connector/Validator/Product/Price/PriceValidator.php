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
        Assertion::float($object->getPrice(), null, 'product.price.price');
        Assertion::greaterOrEqualThan($object->getPrice(), 0.0, null, 'product.price.price');
        Assertion::float($object->getPseudoPrice(), null, 'product.price.pseudoPrice');
        Assertion::greaterOrEqualThan($object->getPseudoPrice(), 0.0, null, 'product.price.pseudoPrice');
        Assertion::nullOrUuid($object->getCustomerGroupIdentifier(), null, 'product.price.customerGroupIdentifier');
        Assertion::float($object->getFromAmount(), null, 'product.price.fromAmount');
        Assertion::greaterOrEqualThan($object->getFromAmount(), 0.0, null, 'product.price.fromAmount');
        Assertion::nullOrFloat($object->getToAmount(), null, 'product.price.toAmount');
    }
}
