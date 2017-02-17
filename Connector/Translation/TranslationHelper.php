<?php

namespace PlentyConnector\Connector\Translation;

use PlentyConnector\Connector\TransferObject\TranslateableInterface;
use PlentyConnector\Connector\ValueObject\Translation\Translation;
use ReflectionClass;

/**
 * Class TranslationHelper
 */
class TranslationHelper implements TranslationHelperInterface
{
    /**
     * @param TranslateableInterface $object
     *
     * @return array
     */
    public function getLanguageIdentifiers(TranslateableInterface $object)
    {
        $languages = [];

        foreach ($object->getTranslations() as $translation) {
            $languageIdentifier = $translation->getLanguageIdentifier();

            if (isset($languages[$languageIdentifier])) {
                continue;
            }

            $languages[$languageIdentifier] = $languageIdentifier;
        }

        return $languages;
    }

    /**
     * {@inheritdoc}
     */
    public function translate($languageIdentifier, TranslateableInterface $object)
    {
        $reflectionClass = new ReflectionClass($object);
        $properties = $reflectionClass->getProperties();

        $translations = array_filter($object->getTranslations(),
            function (Translation $translation) use ($languageIdentifier) {
                return $translation->getLanguageIdentifier() === $languageIdentifier;
            });

        if (empty($translations)) {
            return $object;
        }

        $args = [];
        foreach ($properties as $property) {
            $possibleTranslations = array_filter($translations,
                function (Translation $translation) use ($property) {
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
