<?php

namespace SystemConnector\Validator\VatRate;

use Assert\Assertion;
use SystemConnector\TransferObject\VatRate\VatRate;
use SystemConnector\Validator\ValidatorInterface;

class VatRateValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object) :bool
    {
        return $object instanceof VatRate;
    }

    /**
     * @param VatRate $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getIdentifier(), null, 'vatRate.identifier');
        Assertion::string($object->getName(), null, 'vatRate.name');
        Assertion::notBlank($object->getName(), null, 'vatRate.name');
    }
}
