<?php

namespace PlentymarketsAdapter\ResponseParser\Order;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;

/**
 * Class OrderResponseParser
 */
class OrderResponseParser implements OrderResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * OrderResponseParser constructor.
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
        // TODO: Implement parse() method.
    }
}
