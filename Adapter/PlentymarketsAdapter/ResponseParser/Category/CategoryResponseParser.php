<?php

namespace PlentymarketsAdapter\ResponseParser\Category;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentymarketsAdapter\ResponseParser\ResponseParserInterface;

/**
 * Class CategoryResponseParser
 */
class CategoryResponseParser implements ResponseParserInterface
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
     * TODO: Implement
     *
     * @param array $entry
     *
     * @return TransferObjectInterface[]
     */
    public function parse(array $entry)
    {
        return null;
    }
}
