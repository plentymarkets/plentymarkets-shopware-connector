<?php

namespace PlentyConnector\Connector\Validator\Customer;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Order\Customer\Customer;
use PlentyConnector\Connector\Validator\ValidatorInterface;

/**
 * Class CustomerValidator
 */
class CustomerValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof Customer;
    }

    /**
     * @param Customer $object
     */
    public function validate($object)
    {
        Assertion::inArray($object->getType(), $object->getCustomerTypes());
        Assertion::string($object->getNumber());
        Assertion::notBlank($object->getNumber());
        Assertion::email($object->getEmail());
        Assertion::uuid($object->getLanguageIdentifier());
        Assertion::uuid($object->getCustomerGroupIdentifier());
        Assertion::inArray($object->getSalutation(), $object->getSalutations());
        Assertion::nullOrString($object->getTitle());
        Assertion::string($object->getFirstname());
        Assertion::notBlank($object->getFirstname());
        Assertion::string($object->getLastname());
        Assertion::notBlank($object->getLastname());
        Assertion::nullOrIsInstanceOf($object->getBirthday(), \DateTimeImmutable::class);
        Assertion::nullOrString($object->getPhoneNumber());
        Assertion::nullOrString($object->getMobilePhoneNumber());
        Assertion::uuid($object->getShopIdentifier());
    }
}
