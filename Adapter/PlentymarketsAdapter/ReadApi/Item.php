<?php

namespace PlentymarketsAdapter\ReadApi;

use PlentymarketsAdapter\Helper\LanguageHelper;

class Item extends ApiAbstract
{
    public function findOne($productId)
    {
        $languageHelper = new LanguageHelper();

        return $this->client->request('GET', 'items/' . $productId, [
            'lang' => $languageHelper->getLanguagesQueryString(),
        ]);
    }

    public function findAll()
    {
        $languageHelper = new LanguageHelper();

        return $this->client->request('GET', 'items', [
            'lang' => $languageHelper->getLanguagesQueryString(),
        ]);
    }

    public function findChanged($startTimestamp, $endTimestamp)
    {
        $languageHelper = new LanguageHelper();

        return $this->client->request('GET', 'items', [
            'lang' => $languageHelper->getLanguagesQueryString(),
            'updatedBetween' => $startTimestamp . ',' . $endTimestamp,
        ]);
    }
}
