<?php

namespace PlentyConnector\Connector\Validator\Order\Address;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Order\Address\Address;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;

/**
 * Class AddressValidator
 */
class AddressValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof Address;
    }

    /**
     * @param Address $object
     */
    public function validate($object)
    {
        Assertion::nullOrString($object->getCompany());
        Assertion::nullOrString($object->getDepartment());
        Assertion::inArray($object->getSalutation(), $object->getSalutations());
        Assertion::nullOrString($object->getTitle());
        Assertion::nullOrString($object->getFirstname());
        Assertion::nullOrString($object->getLastname());
        Assertion::string($object->getStreet());
        Assertion::notBlank($object->getStreet());
        Assertion::string($object->getZipcode());
        Assertion::notBlank($object->getZipcode());
        Assertion::string($object->getCity());
        Assertion::notBlank($object->getCity());
        Assertion::uuid($object->getCountryIdentifier());
        Assertion::nullOrString($object->getVatId());
        Assertion::nullOrString($object->getPhoneNumber());
        Assertion::nullOrString($object->getMobilePhoneNumber());
        Assertion::allIsInstanceOf($object->getAttributes(), Attribute::class);
    }
}
