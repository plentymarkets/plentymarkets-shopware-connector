<?php

namespace PlentyConnector\Connector\Translation;

use PlentyConnector\Connector\TransferObject\TranslateableInterface;
use PlentyConnector\Connector\ValueObject\Translation\Translation;

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
        /**
         * @var Translation[] $translations
         */
        $translations = array_filter($object->getTranslations(), function (Translation $translation) use ($languageIdentifier) {
            return $translation->getLanguageIdentifier() === $languageIdentifier;
        });

        if (empty($translations)) {
            return $object;
        }

        foreach ($translations as $translation) {
            $method = 'set' . ucfirst($translation->getProperty());

            if (method_exists($object, $method)) {
                $object->$method($translation->getValue());
            }
        }

        return $object;
    }
}
