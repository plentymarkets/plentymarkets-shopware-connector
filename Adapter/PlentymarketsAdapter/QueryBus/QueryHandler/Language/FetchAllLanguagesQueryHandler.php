<?php

namespace PlentymarketsAdapter\QueryBus\QueryHandler\Language;

use PlentyConnector\Connector\QueryBus\Query\Language\FetchAllLanguagesQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\ResponseParserInterface;

/**
 * Class FetchAllLanguagesQueryHandler
 */
class FetchAllLanguagesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ResponseParserInterface
     */
    private $responseParser;

    /**
     * FetchAllLanguagesQueryHandler constructor.
     *
     * @param ResponseParserInterface $responseParser
     */
    public function __construct(ResponseParserInterface $responseParser)
    {
        $this->responseParser = $responseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $event)
    {
        return $event instanceof FetchAllLanguagesQuery &&
            $event->getAdapterName() === PlentymarketsAdapter::getName();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $event)
    {
        $languages = array_map(function ($language) {
            return $this->responseParser->parse($language);
        }, $this->getLanguages());

        return array_filter($languages);
    }

    /**
     * @return array
     */
    private function getLanguages()
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
}
