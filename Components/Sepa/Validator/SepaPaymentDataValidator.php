<?php

namespace PlentyConnector\Components\Sepa\Validator;

use Assert\Assertion;
use PlentyConnector\Components\Sepa\PaymentData\SepaPaymentData;
use PlentyConnector\Connector\Validator\ValidatorInterface;

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
        Assertion::string($object->getAccountOwner(), null, 'components.sepa.accountOwner');
        Assertion::notBlank($object->getAccountOwner(), null, 'components.sepa.accountOwner');
        Assertion::string($object->getIban(), null, 'components.sepa.iban');
        Assertion::notBlank($object->getIban(), null, 'components.sepa.iban');
        Assertion::nullOrString($object->getBic(), null, 'components.sepa.bic');
    }
}
