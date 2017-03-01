<?php

namespace PlentymarketsAdapter\Helper;

/**
 * Class LanguageHelper
 */
class LanguageHelper
{
    /**
     * Returns a list of all supported languages from plentymarkets
     * source: https://developers.plentymarkets.com/rest-doc/introduction#languages
     *
     * @return array
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
     * @return string
     */
    public function getLanguagesQueryString()
    {
        return implode(',', array_column($this->getLanguages(), 'id'));
    }
}
