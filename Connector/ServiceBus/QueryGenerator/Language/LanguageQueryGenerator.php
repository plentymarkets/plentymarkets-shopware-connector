<?php

namespace PlentyConnector\Connector\ServiceBus\QueryGenerator\Language;

use PlentyConnector\Connector\ServiceBus\Query\Language\FetchAllLanguagesQuery;
use PlentyConnector\Connector\ServiceBus\Query\Language\FetchChangedLanguagesQuery;
use PlentyConnector\Connector\ServiceBus\Query\Language\FetchLanguageQuery;
use PlentyConnector\Connector\ServiceBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\TransferObject\Language\Language;

/**
 * Class LanguageQueryGenerator
 */
class LanguageQueryGenerator implements QueryGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === Language::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllLanguagesQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchChangedQuery($adapterName)
    {
        return new FetchChangedLanguagesQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchLanguageQuery($adapterName, $identifier);
    }
}
