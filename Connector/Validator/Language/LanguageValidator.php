<?php

namespace PlentyConnector\Connector\Validator\Language;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Language\Language;
use PlentyConnector\Connector\Validator\ValidatorInterface;

/**
 * Class LanguageValidator
 */
class LanguageValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof Language;
    }

    /**
     * @param Language $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getIdentifier());
        Assertion::string($object->getName());
        Assertion::notBlank($object->getName());
    }
}
