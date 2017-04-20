<?php

namespace PlentyConnector\Payment\PayPal\Validator;

use Assert\Assertion;
use DateTimeImmutable;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Payment\PayPal\PaymentData\PayPalPlusInvoicePaymentData;

/**
 * Class PayPalPlusInvoicePaymentDataValidator
 */
class PayPalPlusInvoicePaymentDataValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof PayPalPlusInvoicePaymentData;
    }

    /**
     * @param PayPalPlusInvoicePaymentData $object
     */
    public function validate($object)
    {
        Assertion::string($object->getReferenceNumber(), null, 'payment.paypal.invoice.referenceNumber');
        Assertion::notBlank($object->getReferenceNumber(), null, 'payment.paypal.invoice.referenceNumber');

        Assertion::string($object->getInstructionType(), null, 'payment.paypal.invoice.instructionType');
        Assertion::notBlank($object->getInstructionType(), null, 'payment.paypal.invoice.instructionType');

        Assertion::string($object->getBankName(), null, 'payment.paypal.invoice.bankName');
        Assertion::notBlank($object->getBankName(), null, 'payment.paypal.invoice.bankName');

        Assertion::string($object->getAccountHolderName(), null, 'payment.paypal.invoice.accountHolderName');
        Assertion::notBlank($object->getAccountHolderName(), null, 'payment.paypal.invoice.accountHolderName');

        Assertion::string($object->getInternationalBankAccountNumber(), null, 'payment.paypal.invoice.internationalBankAccountNumber');
        Assertion::notBlank($object->getInternationalBankAccountNumber(), null, 'payment.paypal.invoice.internationalBankAccountNumber');

        Assertion::string($object->getBankIdentifierCode(), null, 'payment.paypal.invoice.bankIdentifierCode');
        Assertion::notBlank($object->getBankIdentifierCode(), null, 'payment.paypal.invoice.bankIdentifierCode');

        Assertion::string($object->getAmountValue(), null, 'payment.paypal.invoice.amountValue');
        Assertion::notBlank($object->getAmountValue(), null, 'payment.paypal.invoice.amountValue');

        Assertion::string($object->getAmountCurrency(), null, 'payment.paypal.invoice.amountCurrency');
        Assertion::notBlank($object->getAmountCurrency(), null, 'payment.paypal.invoice.amountCurrency');

        Assertion::isInstanceOf($object->getPaymentDueDate(), DateTimeImmutable::class, null,'payment.paypal.invoice.amountCurrency');
    }
}
