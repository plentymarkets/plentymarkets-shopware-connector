<?php

namespace SystemConnector\Validator\Language;

use Assert\Assertion;
use SystemConnector\TransferObject\Language\Language;
use SystemConnector\Validator\ValidatorInterface;

class LanguageValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object) :bool
    {
        return $object instanceof Language;
    }

    /**
     * @param Language $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getIdentifier(), null, 'language.identifier');
        Assertion::string($object->getName(), null, 'language.name');
        Assertion::notBlank($object->getName(), null, 'language.name');
    }
}
