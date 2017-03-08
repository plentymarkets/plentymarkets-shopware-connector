<?php

namespace PlentyConnector\Connector\Validator\Shop;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentyConnector\Connector\Validator\ValidatorInterface;

/**
 * Class ShopValidator
 */
class ShopValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
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
