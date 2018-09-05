<?php

namespace PlentymarketsAdapter\Helper;

class LanguageHelper implements LanguageHelperInterface
{
    /**
     * {@inheritdoc}
     */
    public function getLanguages()
    {
        return [
            [
                'id' => 'bg',
                'name' => 'Bulgarian',
            ],
            [
                'id' => 'cn',
                'name' => 'Chinese',
            ],
            [
                'id' => 'cz',
                'name' => 'Czech',
            ],
            [
                'id' => 'da',
                'name' => 'Danish',
            ],
            [
                'id' => 'de',
                'name' => 'German',
            ],
            [
                'id' => 'en',
                'name' => 'English',
            ],
            [
                'id' => 'es',
                'name' => 'Spanish',
            ],
            [
                'id' => 'fr',
                'name' => 'French',
            ],
            [
                'id' => 'it',
                'name' => 'Italian',
            ],
            [
                'id' => 'nl',
                'name' => 'Dutch',
            ],
            [
                'id' => 'nn',
                'name' => 'Norwegian',
            ],
            [
                'id' => 'pl',
                'name' => 'Polish',
            ],
            [
                'id' => 'pt',
                'name' => 'Portuguese',
            ],
            [
                'id' => 'ro',
                'name' => 'Romanian',
            ],
            [
                'id' => 'ru',
                'name' => 'Russian',
            ],
            [
                'id' => 'se',
                'name' => 'Swedish',
            ],
            [
                'id' => 'sk',
                'name' => 'Slovak',
            ],
            [
                'id' => 'tr',
                'name' => 'Turkish',
            ],
            [
                'id' => 'vn',
                'name' => 'Vietnamese',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getLanguagesQueryString()
    {
        return implode(',', array_column($this->getLanguages(), 'id'));
    }
}
