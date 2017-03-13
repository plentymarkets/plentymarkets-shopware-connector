<?php

namespace PlentyConnector\Connector\Validator\Order\PaymentData;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Order\Payment\Payment;
use PlentyConnector\Connector\TransferObject\Order\PaymentData\PaymentDataInterface;
use PlentyConnector\Connector\TransferObject\Order\PaymentData\SepaPaymentData;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;

/**
 * Class PaymentDataValidator
 */
class PaymentDataValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof PaymentDataInterface;
    }

    /**
     * @param $object
     */
    public function validate($object)
    {
        if ($object instanceof SepaPaymentData) {
            Assertion::string($object->getAccountOwner(), null, 'paymentData.accountOwner');
            Assertion::notBlank($object->getAccountOwner(), null, 'paymentData.accountOwner');
            Assertion::string($object->getIban(), null, 'paymentData.iban');
            Assertion::notBlank($object->getIban(), null, 'paymentData.iban');
            Assertion::nullOrString($object->getBic(), null, 'paymentData.bic');
        }
    }
}
