<?php

namespace PlentymarketsAdapter\ResponseParser\PaymentStatus;

use PlentymarketsAdapter\PlentymarketsAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\PaymentStatus\PaymentStatus;

class PaymentStatusResponseParser implements PaymentStatusResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

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
            PaymentStatus::TYPE
        );

        return PaymentStatus::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => $this->getName($entry),
        ]);
    }

    /**
     * @param array $entry
     */
    private function getName($entry): string
    {
        if (empty($entry['names'])) {
            return $entry['id'];
        }

        $names = $entry['names'];

        if (!empty($names)) {
            return array_shift($names);
        }

        return $entry['id'];
    }
}
