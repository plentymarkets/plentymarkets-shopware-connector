<?php

namespace PlentyConnector\Connector\Validator\Translation;

use Assert\Assertion;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Connector\ValueObject\Translation\Translation;

/**
 * Class TranslationValidator
 */
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
