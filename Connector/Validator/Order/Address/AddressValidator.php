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
        Assertion::nullOrString($object->getCompany(), null, 'address.company');
        Assertion::nullOrString($object->getDepartment(), null, 'address.department');
        Assertion::inArray($object->getSalutation(), $object->getSalutations(), null, 'address.saluation');
        Assertion::nullOrString($object->getTitle(), null, 'address.title');
        Assertion::nullOrString($object->getFirstname(), null, 'address.firstname');
        Assertion::nullOrString($object->getLastname(), null, 'address.lastname');
        Assertion::string($object->getStreet(), null, 'address.street');
        Assertion::notBlank($object->getStreet(), null, 'address.street');
        Assertion::string($object->getZipcode(), null, 'address.zipcode');
        Assertion::notBlank($object->getZipcode(), null, 'address.zipcode');
        Assertion::string($object->getCity(), null, 'address.city');
        Assertion::notBlank($object->getCity(), null, 'address.city');
        Assertion::uuid($object->getCountryIdentifier(), null, 'address.countryIdentifier');
        Assertion::nullOrString($object->getVatId(), null, 'address.vatId');
        Assertion::nullOrString($object->getPhoneNumber(), null, 'address.phoneNumber');
        Assertion::nullOrString($object->getMobilePhoneNumber(), null, 'address.mobilePhoneNumber');
        Assertion::allIsInstanceOf($object->getAttributes(), Attribute::class, null, 'address.attributes');
    }
}
