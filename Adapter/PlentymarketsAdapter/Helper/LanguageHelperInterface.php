<?php

namespace PlentymarketsAdapter\Helper;

/**
 * Interface LanguageHelperInterface
 */
interface LanguageHelperInterface
{
    /**
     * Returns a list of all supported languages from plentymarkets
     * source: https://developers.plentymarkets.com/rest-doc/introduction#languages
     *
     * @return array
     */
    public function getLanguages();

    /**
     * @return string
     */
    public function getLanguagesQueryString();
}
