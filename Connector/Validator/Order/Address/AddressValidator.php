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
        Assertion::nullOrNotBlank($object->getCompany(), null, 'order.address.company');
        Assertion::nullOrNotBlank($object->getDepartment(), null, 'order.address.department');
        Assertion::inArray($object->getSalutation(), $object->getSalutations(), null, 'order.address.saluation');
        Assertion::nullOrNotBlank($object->getTitle(), null, 'order.address.title');
        Assertion::nullOrNotBlank($object->getFirstname(), null, 'order.address.firstname');
        Assertion::nullOrNotBlank($object->getLastname(), null, 'order.address.lastname');
        Assertion::string($object->getStreet(), null, 'order.address.street');
        Assertion::notBlank($object->getStreet(), null, 'order.address.street');
        Assertion::nullOrNotBlank($object->getAdditional(), null, 'order.address.additional');
        Assertion::string($object->getPostalCode(), null, 'order.address.postalCode');
        Assertion::notBlank($object->getPostalCode(), null, 'order.address.postalCode');
        Assertion::string($object->getCity(), null, 'order.address.city');
        Assertion::notBlank($object->getCity(), null, 'order.address.city');
        Assertion::uuid($object->getCountryIdentifier(), null, 'order.address.countryIdentifier');
        Assertion::nullOrNotBlank($object->getVatId(), null, 'order.address.vatId');
        Assertion::nullOrNotBlank($object->getPhoneNumber(), null, 'order.address.phoneNumber');
        Assertion::nullOrNotBlank($object->getMobilePhoneNumber(), null, 'order.address.mobilePhoneNumber');
        Assertion::allIsInstanceOf($object->getAttributes(), Attribute::class, null, 'order.address.attributes');
    }
}
