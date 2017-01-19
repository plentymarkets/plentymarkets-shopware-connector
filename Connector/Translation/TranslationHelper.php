<?php

namespace PlentyConnector\Connector\Translation;

use PlentyConnector\Connector\TransferObject\TranslateableInterface;
use PlentyConnector\Connector\ValueObject\Translation\TranslationInterface;

/**
 * Class TranslationHelper
 */
class TranslationHelper implements TranslationHelperInterface
{
    /**
     * {@inheritdoc}
     */
    public function translate($languageIdentifier, TranslateableInterface $object)
    {
        $reflectionClass = new \ReflectionClass($object);
        $properties = $reflectionClass->getProperties();

        $translations = array_filter($object->getTranslations(), function(TranslationInterface $translation) use ($languageIdentifier) {
            return $translation->getLanguageIdentifier() === $languageIdentifier;
        });

        if (empty($translations)) {
            return $object;
        }

        $args = [];
        foreach ($properties as $property) {
            $possibleTranslations = array_filter($translations, function(TranslationInterface $translation) use ($property) {
                return $translation->getProperty() === $property->getName();
            });

            if (empty($possibleTranslations)) {
                $property->setAccessible(true);
                $args[$property->getName()] = $property->getValue($object);

                continue;
            }

            /**
             * @var TranslationInterface $translation
             */
            $translation = array_shift($possibleTranslations);

            $args[$property->getName()] = $translation->getValue();
        }

        return $reflectionClass->newInstanceArgs($args);
    }
}
