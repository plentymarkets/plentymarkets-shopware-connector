<?php

namespace PlentymarketsAdapter\ResponseParser\PaymentStatus;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\PaymentStatus\PaymentStatus;
use PlentymarketsAdapter\PlentymarketsAdapter;

/**
 * Class PaymentStatusResponseParser
 */
class PaymentStatusResponseParser implements PaymentStatusResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * PaymentStatusResponseParser constructor.
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
            return null;
        }

        $identity = $this->identityService->findOneOrCreate(
            (string) $entry['id'],
            PlentymarketsAdapter::NAME,
            PaymentStatus::TYPE
        );

        return PaymentStatus::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => $this->getName($entry),
        ]);
    }

    /**
     * @param $entry
     *
     * @return string
     */
    private function getName($entry)
    {
        if (empty($entry['names'])) {
            return $entry['id'];
        }

        $names = array_filter(array_column($entry['names'], 'backendName'));

        if (!empty($names)) {
            return array_shift($names);
        }

        return $entry['id'];
    }
}
