<?php

namespace PlentyConnector\Connector\Validator\Order\Package;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Order\Package\Package;
use PlentyConnector\Connector\Validator\ValidatorInterface;

/**
 * Class PackageValidator
 */
class PackageValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof Package;
    }

    /**
     * @param Package $object
     */
    public function validate($object)
    {
        Assertion::isInstanceOf($object->getShippingTime(), \DateTimeImmutable::class, null, 'order.package.shippingTime');
        Assertion::string($object->getShippingCode(), null, 'order.package.shippingCode');
        Assertion::nullOrNotBlank($object->getShippingProvider(), null, 'order.package.shippingProvider');
    }
}
