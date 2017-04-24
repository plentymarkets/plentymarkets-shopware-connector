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
        Assertion::nullOrNotBlank($object->getCompany(), null, 'address.company');
        Assertion::nullOrNotBlank($object->getDepartment(), null, 'address.department');
        Assertion::inArray($object->getSalutation(), $object->getSalutations(), null, 'address.saluation');
        Assertion::nullOrNotBlank($object->getTitle(), null, 'address.title');
        Assertion::nullOrNotBlank($object->getFirstname(), null, 'address.firstname');
        Assertion::nullOrNotBlank($object->getLastname(), null, 'address.lastname');
        Assertion::string($object->getStreet(), null, 'address.street');
        Assertion::notBlank($object->getStreet(), null, 'address.street');
        Assertion::nullOrNotBlank($object->getAdditional(), null, 'address.additional');
        Assertion::string($object->getPostalCode(), null, 'address.postalCode');
        Assertion::notBlank($object->getPostalCode(), null, 'address.postalCode');
        Assertion::string($object->getCity(), null, 'address.city');
        Assertion::notBlank($object->getCity(), null, 'address.city');
        Assertion::uuid($object->getCountryIdentifier(), null, 'address.countryIdentifier');
        Assertion::nullOrNotBlank($object->getVatId(), null, 'address.vatId');
        Assertion::nullOrNotBlank($object->getPhoneNumber(), null, 'address.phoneNumber');
        Assertion::nullOrNotBlank($object->getMobilePhoneNumber(), null, 'address.mobilePhoneNumber');
        Assertion::allIsInstanceOf($object->getAttributes(), Attribute::class, null, 'address.attributes');
    }
}
