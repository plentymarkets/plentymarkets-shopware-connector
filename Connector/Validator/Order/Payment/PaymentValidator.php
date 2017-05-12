<?php

namespace PlentyConnector\Connector\Validator\Order\Payment;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Payment\Payment;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;

/**
 * Class PaymentValidator
 */
class PaymentValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof Payment;
    }

    /**
     * @param Payment $object
     */
    public function validate($object)
    {
        Assertion::float($object->getAmount(), null, 'order.payment.amount');
        Assertion::uuid($object->getCurrencyIdentifier(), null, 'order.currencyIdentifier');
        Assertion::uuid($object->getPaymentMethodIdentifier(), null, 'order.paymentMethodIdentifier');
        Assertion::uuid($object->getOrderIdentifer(), null, 'order.orderIdentifier');
        Assertion::string($object->getTransactionReference(), null, 'order.transactionReference');
        Assertion::notBlank($object->getTransactionReference(), null, 'order.transactionReference');
        Assertion::allIsInstanceOf($object->getAttributes(), Attribute::class, null, 'order.attributes');
    }
}
