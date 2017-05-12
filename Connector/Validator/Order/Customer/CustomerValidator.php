<?php

namespace PlentyConnector\Connector\Validator\Order\Customer;

use Assert\Assertion;
use DateTimeImmutable;
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
        Assertion::inArray($object->getType(), $object->getCustomerTypes(), null, 'order.customer.type');

        Assertion::string($object->getNumber(), 'order.customer.number');
        Assertion::notBlank($object->getNumber(), 'order.customer.number');

        Assertion::email($object->getEmail(), 'order.customer.email');

        Assertion::uuid($object->getLanguageIdentifier(), 'order.customer.languageIdentifier');

        Assertion::uuid($object->getCustomerGroupIdentifier(), 'order.customer.customerGroupIdentifier');

        Assertion::inArray($object->getSalutation(), $object->getSalutations(), 'order.customer.salutation');

        Assertion::nullOrNotBlank($object->getTitle(), 'order.customer.title');

        Assertion::string($object->getFirstname(), 'order.customer.firstname');
        Assertion::notBlank($object->getFirstname(), 'order.customer.firstname');

        Assertion::string($object->getLastname(), 'order.customer.lastname');
        Assertion::notBlank($object->getLastname(), 'order.customer.lastname');

        Assertion::nullOrIsInstanceOf($object->getBirthday(), DateTimeImmutable::class, 'order.customer.birthday');

        Assertion::nullOrNotBlank($object->getPhoneNumber(), 'order.customer.phoneNumber');
        Assertion::nullOrNotBlank($object->getMobilePhoneNumber(), 'order.customer.mobilePhoneNumber');

        Assertion::uuid($object->getShopIdentifier(), 'order.customer.shopIdentifier');
    }
}
