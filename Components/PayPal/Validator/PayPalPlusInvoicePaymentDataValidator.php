<?php

namespace PlentyConnector\Components\PayPal\Validator;

use Assert\Assertion;
use DateTimeImmutable;
use PlentyConnector\Components\PayPal\PaymentData\PayPalPlusInvoicePaymentData;
use SystemConnector\Validator\ValidatorInterface;

class PayPalPlusInvoicePaymentDataValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object): bool
    {
        return $object instanceof PayPalPlusInvoicePaymentData;
    }

    /**
     * @param PayPalPlusInvoicePaymentData $object
     */
    public function validate($object)
    {
        Assertion::string($object->getReferenceNumber(), null, 'components.paypal.invoice.referenceNumber');
        Assertion::notBlank($object->getReferenceNumber(), null, 'components.paypal.invoice.referenceNumber');

        Assertion::string($object->getInstructionType(), null, 'components.paypal.invoice.instructionType');
        Assertion::notBlank($object->getInstructionType(), null, 'components.paypal.invoice.instructionType');

        Assertion::string($object->getBankName(), null, 'components.paypal.invoice.bankName');
        Assertion::notBlank($object->getBankName(), null, 'components.paypal.invoice.bankName');

        Assertion::string($object->getAccountHolderName(), null, 'components.paypal.invoice.accountHolderName');
        Assertion::notBlank($object->getAccountHolderName(), null, 'components.paypal.invoice.accountHolderName');

        Assertion::string($object->getInternationalBankAccountNumber(), null, 'components.paypal.invoice.internationalBankAccountNumber');
        Assertion::notBlank($object->getInternationalBankAccountNumber(), null, 'components.paypal.invoice.internationalBankAccountNumber');

        Assertion::string($object->getBankIdentifierCode(), null, 'components.paypal.invoice.bankIdentifierCode');
        Assertion::notBlank($object->getBankIdentifierCode(), null, 'components.paypal.invoice.bankIdentifierCode');

        Assertion::string($object->getAmountValue(), null, 'components.paypal.invoice.amountValue');
        Assertion::notBlank($object->getAmountValue(), null, 'components.paypal.invoice.amountValue');

        Assertion::string($object->getAmountCurrency(), null, 'components.paypal.invoice.amountCurrency');
        Assertion::notBlank($object->getAmountCurrency(), null, 'components.paypal.invoice.amountCurrency');

        Assertion::isInstanceOf($object->getPaymentDueDate(), DateTimeImmutable::class, null, 'components.paypal.invoice.paymentDueDate');
    }
}
