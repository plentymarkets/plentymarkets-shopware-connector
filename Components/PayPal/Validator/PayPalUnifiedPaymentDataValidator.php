<?php

namespace PlentyConnector\Components\PayPal\Validator;

use Assert\Assertion;
use DateTimeImmutable;
use PlentyConnector\Components\PayPal\PaymentData\PayPalUnifiedPaymentData;
use SystemConnector\Validator\ValidatorInterface;

class PayPalUnifiedPaymentDataValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object): bool
    {
        return $object instanceof PayPalUnifiedPaymentData;
    }

    /**
     * @param PayPalUnifiedPaymentData $object
     */
    public function validate($object)
    {
        Assertion::string($object->getReference(), null, 'components.paypal.unified.reference');
        Assertion::notBlank($object->getReference(), null, 'components.paypal.unified.reference');

        Assertion::string($object->getBankName(), null, 'components.paypal.unified.bankName');
        Assertion::notBlank($object->getBankName(), null, 'components.paypal.unified.bankName');

        Assertion::string($object->getAccountHolder(), null, 'components.paypal.unified.accountHolder');
        Assertion::notBlank($object->getAccountHolder(), null, 'components.paypal.unified.accountHolder');

        Assertion::string($object->getIban(), null, 'components.paypal.unified.iban');
        Assertion::notBlank($object->getIban(), null, 'components.paypal.unified.iban');

        Assertion::string($object->getBic(), null, 'components.paypal.unified.bic');
        Assertion::notBlank($object->getBic(), null, 'components.paypal.unified.bic');

        Assertion::string($object->getAmount(), null, 'components.paypal.unified.amount');
        Assertion::notBlank($object->getAmount(), null, 'components.paypal.unified.amount');

        Assertion::isInstanceOf($object->getDueDate(), DateTimeImmutable::class, null, 'components.paypal.unified.dueDate');
    }
}
