<?php

namespace PlentyConnector\Components\Bundle\Validator\BundleProduct;

use Assert\Assertion;
use PlentyConnector\Components\Bundle\TransferObject\BundleProduct\BundleProduct;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;

class BundleProductValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof BundleProduct;
    }

    /**
     * @param BundleProduct $object
     */
    public function validate($object)
    {
        Assertion::string($object->getNumber(), null, 'components.bundle.product.number');
        Assertion::notEmpty($object->getNumber(), null, 'components.bundle.product.number');

        Assertion::float($object->getAmount(), null, 'components.bundle.product.amount');
        Assertion::greaterOrEqualThan($object->getAmount(), 0, null, 'components.bundle.product.amount');

        Assertion::integer($object->getPosition(), null, 'components.bundle.product.position');

        Assertion::allIsInstanceOf($object->getAttributes(), Attribute::class, null, 'components.bundle.product.attributes');
    }
}
