<?php

namespace PlentymarketsAdapter\ResponseParser\PaymentMethod;

use PlentymarketsAdapter\PlentymarketsAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\PaymentMethod\PaymentMethod;

class PaymentMethodResponseParser implements PaymentMethodResponseParserInterface
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
        // https://forum.plentymarkets.com/t/paymentmethods-fehlen-im-payment-methods-response/69372/8
        if ($entry['id'] === 6000) {
            $entry['id'] = 0;
        }

        $identity = $this->identityService->findOneOrCreate(
            (string) $entry['id'],
            PlentymarketsAdapter::NAME,
            PaymentMethod::TYPE
        );

        return PaymentMethod::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'name' => $entry['name'],
        ]);
    }
}
