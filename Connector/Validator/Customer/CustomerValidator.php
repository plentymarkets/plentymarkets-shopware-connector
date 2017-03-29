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
        Assertion::inArray($object->getType(), $object->getCustomerTypes(), null, 'customer.type');

        Assertion::string($object->getNumber(), 'customer.number');
        Assertion::notBlank($object->getNumber(), 'customer.number');

        Assertion::email($object->getEmail(), 'customer.email');

        Assertion::uuid($object->getLanguageIdentifier(), 'customer.languageIdentifier');

        Assertion::uuid($object->getCustomerGroupIdentifier(), 'customer.customerGroupIdentifier');

        Assertion::inArray($object->getSalutation(), $object->getSalutations(), 'customer.salutation');

        Assertion::nullOrString($object->getTitle(), 'customer.title');

        Assertion::string($object->getFirstname(), 'customer.firstname');
        Assertion::notBlank($object->getFirstname(), 'customer.firstname');

        Assertion::string($object->getLastname(), 'customer.lastname');
        Assertion::notBlank($object->getLastname(), 'customer.lastname');

        Assertion::nullOrIsInstanceOf($object->getBirthday(), \DateTimeImmutable::class, 'customer.birthday');

        Assertion::nullOrString($object->getPhoneNumber(), 'customer.phoneNumber');
        Assertion::nullOrString($object->getMobilePhoneNumber(), 'customer.mobilePhoneNumber');

        Assertion::uuid($object->getShopIdentifier(), 'customer.shopIdentifier');
    }
}
