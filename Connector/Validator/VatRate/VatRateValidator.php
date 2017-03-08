<?php

namespace PlentyConnector\Connector\Validator\VatRate;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\VatRate\VatRate;
use PlentyConnector\Connector\Validator\ValidatorInterface;

/**
 * Class VatRateValidator
 */
class VatRateValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof VatRate;
    }

    /**
     * @param VatRate $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getIdentifier());
        Assertion::string($object->getName());
        Assertion::notBlank($object->getName());
    }
}
