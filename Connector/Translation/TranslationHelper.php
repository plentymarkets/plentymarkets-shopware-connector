<?php

namespace SystemConnector\Translation;

use DeepCopy\DeepCopy;
use SystemConnector\TransferObject\TranslatableInterface;
use SystemConnector\ValueObject\Translation\Translation;

class TranslationHelper implements TranslationHelperInterface
{
    /**
     * @param TranslatableInterface $object
     *
     * @return array
     */
    public function getLanguageIdentifiers(TranslatableInterface $object) :array
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
    public function translate($languageIdentifier, TranslatableInterface $object) :TranslatableInterface
    {
        $deepCopy = new DeepCopy();
        $object = $deepCopy->copy($object);

        /**
         * @var Translation[] $translations
         */
        $translations = array_filter($object->getTranslations(), static function (Translation $translation) use ($languageIdentifier) {
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
