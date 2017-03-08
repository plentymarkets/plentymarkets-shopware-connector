<?php

namespace PlentyConnector\Connector\Validator\Order;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Order\Address\Address;
use PlentyConnector\Connector\TransferObject\Order\Comment\Comment;
use PlentyConnector\Connector\TransferObject\Order\Customer\Customer;
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
        Assertion::uuid($object->getIdentifier(), null, 'order.identifier');
        Assertion::inArray($object->getOrderType(), $object->getOrderTypes(), null, 'order.orderType');
        Assertion::string($object->getOrderNumber(), null, 'order.orderNumber');
        Assertion::notBlank($object->getOrderNumber(), null, 'order.orderNumber');
        Assertion::isInstanceOf($object->getOrderTime(), \DateTimeImmutable::class, null, 'order.orderTime');
        Assertion::isInstanceOf($object->getCustomer(), Customer::class, null, 'order.customer');
        Assertion::nullOrIsInstanceOf($object->getBillingAddress(), Address::class, null, 'order.billingAddress');
        Assertion::nullOrIsInstanceOf($object->getShippingAddress(), Address::class, null, 'order.shippingAddress');
        Assertion::allIsInstanceOf($object->getOrderItems(), OrderItem::class, null, 'order.orderItems');
        Assertion::allIsInstanceOf($object->getPayments(), Payment::class, null, 'order.payments');
        Assertion::uuid($object->getShopIdentifier(), null, 'order.shopIdentifier');
        Assertion::uuid($object->getCurrencyIdentifier(), null, 'order.currencyIdentifier');
        Assertion::uuid($object->getOrderStatusIdentifier(), null, 'order.orderStatusIdentifier');
        Assertion::uuid($object->getPaymentStatusIdentifier(), null, 'order.paymentStatusIdentifier');
        Assertion::uuid($object->getPaymentMethodIdentifier(), null, 'order.paymentMethodIdentifier');
        Assertion::uuid($object->getShippingProfileIdentifier(), null, 'order.shippingProfileIdentifier');
        Assertion::allIsInstanceOf($object->getComments(), Comment::class, null, 'order.comments');
        Assertion::allIsInstanceOf($object->getAttributes(), Attribute::class, null, 'order.attributes');
    }
}
