<?php

namespace PlentyConnector\Connector\Validator\Order;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Order\Address\Address;
use PlentyConnector\Connector\TransferObject\Order\Comment\Comment;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentyConnector\Connector\TransferObject\Order\OrderItem\OrderItem;
use PlentyConnector\Connector\TransferObject\Order\Payment\Payment;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;

/**
 * Class OrderValidator
 */
class OrderValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof Order;
    }

    /**
     * @param Order $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getIdentifier());
        Assertion::inArray($object->getOrderType(), $object->getOrderTypes());
        Assertion::string($object->getOrderNumber());
        Assertion::notBlank($object->getOrderNumber());
        Assertion::isInstanceOf($object->getOrderTime(), \DateTimeImmutable::class);
        Assertion::nullOrUuid($object->getCustomer());
        Assertion::nullOrIsInstanceOf($object->getBillingAddress(), Address::class);
        Assertion::nullOrIsInstanceOf($object->getShippingAddress(), Address::class);
        Assertion::allIsInstanceOf($object->getOrderItems(), OrderItem::class);
        Assertion::allIsInstanceOf($object->getPayments(), Payment::class);
        Assertion::uuid($object->getShopIdentifier());
        Assertion::uuid($object->getCurrencyIdentifier());
        Assertion::uuid($object->getOrderStatusIdentifier());
        Assertion::uuid($object->getPaymentStatusIdentifier());
        Assertion::uuid($object->getPaymentMethodIdentifier());
        Assertion::uuid($object->getShippingProfileIdentifier());
        Assertion::allIsInstanceOf($object->getComments(), Comment::class);
        Assertion::allIsInstanceOf($object->getAttributes(), Attribute::class);
    }
}
