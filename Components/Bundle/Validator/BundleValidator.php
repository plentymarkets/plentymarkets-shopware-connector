<?php

namespace PlentyConnector\Components\Bundle\Validator;

use Assert\Assertion;
use PlentyConnector\Components\Bundle\TransferObject\Bundle;
use PlentyConnector\Components\Bundle\TransferObject\BundleProduct\BundleProduct;
use PlentyConnector\Connector\TransferObject\Product\Product;
use PlentyConnector\Connector\Validator\ValidatorInterface;

class BundleValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof Bundle;
    }

    /**
     * @param Bundle $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getIdentifier(), null, 'components.bundle.identifier');

        Assertion::isInstanceOf($object->getProduct(), Product::class, null, 'components.bundle.product');

        Assertion::allIsInstanceOf($object->getBundleProducts(), BundleProduct::class, null, 'components.bundle.bundleProducts');
    }
}
