<?php

namespace PlentyConnector\Connector\QueryBus\QueryGenerator\Language;

use PlentyConnector\Connector\QueryBus\Query\Language\FetchAllLanguagesQuery;
use PlentyConnector\Connector\QueryBus\Query\Language\FetchChangedLanguagesQuery;
use PlentyConnector\Connector\QueryBus\Query\Language\FetchLanguageQuery;
use PlentyConnector\Connector\QueryBus\QueryGenerator\QueryGeneratorInterface;
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
        return $transferObjectType === Language::getType();
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
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllLanguagesQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchLanguageQuery($adapterName, $identifier);
    }
}
