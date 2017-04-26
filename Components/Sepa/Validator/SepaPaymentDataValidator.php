<?php

namespace PlentyConnector\Components\Sepa\Validator;

use Assert\Assertion;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Components\Sepa\PaymentData\SepaPaymentData;

/**
 * Class SepaPaymentDataValidator
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
        Assertion::string($object->getAccountOwner(), null, 'payment.sepa.accountOwner');
        Assertion::notBlank($object->getAccountOwner(), null, 'payment.sepa.accountOwner');
        Assertion::string($object->getIban(), null, 'payment.sepa.iban');
        Assertion::notBlank($object->getIban(), null, 'payment.sepa.iban');
        Assertion::nullOrString($object->getBic(), null, 'payment.sepa.bic');
    }
}
