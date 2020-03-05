<?php

namespace PlentymarketsAdapter\Helper;

interface LanguageHelperInterface
{
    /**
     * Returns a list of all supported languages from plentymarkets
     * source: https://developers.plentymarkets.com/rest-doc/introduction#languages
     */
    public function getLanguages(): array;

    public function getLanguagesQueryString(): string;
}
