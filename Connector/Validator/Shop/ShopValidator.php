<?php

namespace SystemConnector\Validator\Shop;

use Assert\Assertion;
use SystemConnector\TransferObject\Shop\Shop;
use SystemConnector\Validator\ValidatorInterface;

class ShopValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object): bool
    {
        return $object instanceof Shop;
    }

    /**
     * @param Shop $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getIdentifier(), null, 'shop.identifier');
        Assertion::string($object->getName(), null, 'shop.name');
        Assertion::notBlank($object->getName(), null, 'shop.name');
    }
}
