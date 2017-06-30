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
        Assertion::float($object->getAmount(), null, 'payment.payment.amount');
        Assertion::uuid($object->getShopIdentifier(), null, 'payment.shopIdentifier');
        Assertion::uuid($object->getCurrencyIdentifier(), null, 'payment.currencyIdentifier');
        Assertion::uuid($object->getPaymentMethodIdentifier(), null, 'payment.paymentMethodIdentifier');
        Assertion::uuid($object->getOrderIdentifer(), null, 'payment.paymentIdentifier');
        Assertion::string($object->getTransactionReference(), null, 'payment.transactionReference');
        Assertion::string($object->getAccountHolder(), null, 'payment.accountHolder');
        Assertion::notBlank($object->getTransactionReference(), null, 'payment.transactionReference');
        Assertion::allIsInstanceOf($object->getAttributes(), Attribute::class, null, 'payment.attributes');
    }
}
