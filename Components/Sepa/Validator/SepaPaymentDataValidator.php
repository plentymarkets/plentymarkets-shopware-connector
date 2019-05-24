<?php

namespace PlentyConnector\Components\Sepa\Validator;

use Assert\Assertion;
use PlentyConnector\Components\Sepa\PaymentData\SepaPaymentData;
use SystemConnector\Validator\ValidatorInterface;

class SepaPaymentDataValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object) :bool
    {
        return $object instanceof SepaPaymentData;
    }

    /**
     * @param SepaPaymentData $object
     */
    public function validate($object)
    {
        Assertion::string($object->getAccountOwner(), null, 'components.sepa.accountOwner');
        Assertion::notBlank($object->getAccountOwner(), null, 'components.sepa.accountOwner');
        Assertion::string($object->getIban(), null, 'components.sepa.iban');
        Assertion::notBlank($object->getIban(), null, 'components.sepa.iban');
        Assertion::nullOrString($object->getBic(), null, 'components.sepa.bic');
    }
}
