<?php

namespace PlentyConnector\Payment\Sepa\Validator;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Customer\BankAccount\BankAccount;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Payment\Sepa\PaymentData\SepaPaymentData;

/**
 * Class BankAccountValidator
 */
class SepaPaymentDataValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof SepaPaymentData;
    }

    /**
     * @param SepaPaymentData $object
     */
    public function validate($object)
    {
        Assertion::string($object->getAccountOwner(), null, 'paymentData.accountOwner');
        Assertion::notBlank($object->getAccountOwner(), null, 'paymentData.accountOwner');
        Assertion::string($object->getIban(), null, 'paymentData.iban');
        Assertion::notBlank($object->getIban(), null, 'paymentData.iban');
        Assertion::nullOrString($object->getBic(), null, 'paymentData.bic');
    }
}
