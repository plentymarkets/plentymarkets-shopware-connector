<?php

namespace SystemConnector\Validator\Order\Package;

use Assert\Assertion;
use DateTimeImmutable;
use SystemConnector\TransferObject\Order\Package\Package;
use SystemConnector\Validator\ValidatorInterface;

class PackageValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object): bool
    {
        return $object instanceof Package;
    }

    /**
     * @param Package $object
     */
    public function validate($object)
    {
        Assertion::isInstanceOf($object->getShippingTime(), DateTimeImmutable::class, null, 'order.package.shippingTime');
        Assertion::string($object->getShippingCode(), null, 'order.package.shippingCode');
        Assertion::nullOrNotBlank($object->getShippingProvider(), null, 'order.package.shippingProvider');
    }
}
