<?php

namespace PlentymarketsAdapter\ResponseParser\Media;

use Assert\Assertion;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Media\Media;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\ResponseParserInterface;

/**
 * Class MediaResponseParser
 */
class MediaResponseParser implements ResponseParserInterface
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
     * @param array $entry
     *
     * @return TransferObjectInterface|null
     */
    public function parse(array $entry)
    {
        Assertion::url($entry['link']);

        if (!array_key_exists('hash', $entry)) {
            $entry['hash'] = sha1_file($entry['link']);
        }

        if (!array_key_exists('name', $entry)) {
            $entry['name'] = '';
        }

        if (!array_key_exists('alternateName', $entry)) {
            $entry['alternateName'] = '';
        }

        if (!array_key_exists('translations', $entry)) {
            $entry['translations'] = [];
        }

        if (!array_key_exists('attributes', $entry)) {
            $entry['attributes'] = [];
        }

        $identity = $this->identityService->findOneOrCreate(
            (string)$entry['hash'],
            PlentymarketsAdapter::NAME,
            Media::TYPE
        );

        return Media::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'link' => $entry['link'],
            'hash' => $entry['hash'],
            'name' => $entry['name'],
            'alternateName' => $entry['alternateName'],
            'translations' => $entry['translations'],
            'attributes' => $entry['attributes'],
        ]);
    }
}
