<?php

namespace PlentyConnector\Components\Klarna\Validator;

use Assert\Assertion;
use PlentyConnector\Components\Klarna\PaymentData\KlarnaPaymentData;
use PlentyConnector\Connector\Validator\ValidatorInterface;

class KlarnaPaymentDataValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof KlarnaPaymentData;
    }

    /**
     * @param KlarnaPaymentData $object
     */
    public function validate($object)
    {
        Assertion::string($object->getShopId(), null, 'components.klarna.shopid');
        Assertion::string($object->getTransactionId(), null, 'components.klarna.transactionid');
        Assertion::string($object->getPclassId(), null, 'components.klarna.pclassid');
    }
}
