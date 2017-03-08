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
        Assertion::uuid($object->getIdentifier(), null, 'country.identifier');
        Assertion::string($object->getName(), null, 'country.name');
        Assertion::notBlank($object->getName(), null, 'country.name');
    }
}
