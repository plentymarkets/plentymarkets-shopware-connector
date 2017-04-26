<?php

namespace PlentyConnector\Components\Bundle\Validator\BundleProduct;

use Assert\Assertion;
use PlentyConnector\Components\Bundle\TransferObject\BundleProduct\BundleProduct;
use PlentyConnector\Connector\Validator\ValidatorInterface;

/**
 * Class BundleProductValidator
 */
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
        Assertion::string($object->getNumber(), null, 'components.bundleProduct.number');
        Assertion::notEmpty($object->getNumber(), null, 'components.bundleProduct.number');

        Assertion::float($object->getAmount(), null, 'components.bundleProduct.amount');
        Assertion::greaterOrEqualThan($object->getAmount(), 0, null, 'components.bundleProduct.amount');
    }
}
