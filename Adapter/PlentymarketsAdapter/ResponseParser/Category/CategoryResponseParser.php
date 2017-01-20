<?php

namespace PlentymarketsAdapter\ResponseParser\Category;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;

/**
 * Class CategoryResponseParser
 */
class CategoryResponseParser implements CategoryResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * CategoryResponseParser constructor.
     *
     * @param IdentityServiceInterface $identityService
     */
    public function __construct(IdentityServiceInterface $identityService)
    {
        $this->identityService = $identityService;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $entry)
    {

    }
}
