<?php

namespace SystemConnector\Validator\Order\Payment;

use Assert\Assertion;
use SystemConnector\TransferObject\Payment\Payment;
use SystemConnector\Validator\ValidatorInterface;
use SystemConnector\ValueObject\Attribute\Attribute;

class PaymentValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object) :bool
    {
        return $object instanceof Payment;
    }

    /**
     * @param Payment $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getIdentifier(), null, 'payment.identifier');
        Assertion::float($object->getAmount(), null, 'payment.payment.amount');
        Assertion::uuid($object->getShopIdentifier(), null, 'payment.shopIdentifier');
        Assertion::uuid($object->getCurrencyIdentifier(), null, 'payment.currencyIdentifier');
        Assertion::uuid($object->getPaymentMethodIdentifier(), null, 'payment.paymentMethodIdentifier');
        Assertion::uuid($object->getOrderIdentifier(), null, 'payment.paymentIdentifier');
        Assertion::string($object->getTransactionReference(), null, 'payment.transactionReference');
        Assertion::string($object->getAccountHolder(), null, 'payment.accountHolder');
        Assertion::notBlank($object->getTransactionReference(), null, 'payment.transactionReference');
        Assertion::allIsInstanceOf($object->getAttributes(), Attribute::class, null, 'payment.attributes');
    }
}
