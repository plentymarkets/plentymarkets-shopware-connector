<?php

namespace PlentymarketsAdapter\ResponseParser\OrderStatus;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\OrderStatus\OrderStatus;
use PlentymarketsAdapter\PlentymarketsAdapter;

/**
 * Class OrderStatusResponseParser
 */
class OrderStatusResponseParser implements OrderStatusResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * OrderStatusResponseParser constructor.
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
        if (empty($entry['id'])) {
            $entry['id'] = $entry['statusId'];
        }

        if (empty($entry['id'])) {
            return null;
        }

        $identity = $this->identityService->findOneOrCreate(
            (string) $entry['id'],
            PlentymarketsAdapter::NAME,
            OrderStatus::TYPE
        );

        return OrderStatus::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => $this->getName($entry),
        ]);
    }

    /**
     * @param array $entry
     *
     * @return string
     */
    private function getName($entry)
    {
        if (empty($entry['names'])) {
            return $entry['id'];
        }

        $names = $entry['names'];

        if (isset($names['backendName'])) {
            $names = array_filter(array_column($names, 'backendName'));
        }

        if (!empty($names)) {
            return array_shift($names);
        }

        return $entry['id'];
    }
}
