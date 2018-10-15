<?php

namespace SystemConnector\Validator\Translation;

use Assert\Assertion;
use SystemConnector\Validator\ValidatorInterface;
use SystemConnector\ValueObject\Translation\Translation;

class TranslationValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof Translation;
    }

    /**
     * @param Translation $object
     */
    public function validate($object)
    {
        Assertion::uuid($object->getLanguageIdentifier(), null, 'translation.languageIdentifier');

        Assertion::string($object->getProperty(), null, 'translation.property');
        Assertion::notBlank($object->getProperty(), null, 'translation.property');
    }
}
