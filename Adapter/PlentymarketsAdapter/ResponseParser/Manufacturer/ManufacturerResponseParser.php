<?php

namespace PlentymarketsAdapter\ResponseParser\Manufacturer;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;
use PlentymarketsAdapter\Helper\MediaCategoryHelper;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Media\MediaResponseParserInterface;

/**
 * Class ManufacturerResponseParser
 */
class ManufacturerResponseParser implements ManufacturerResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var MediaResponseParserInterface
     */
    private $mediaResponseParser;

    /**
     * ManufacturerResponseParser constructor.
     *
     * @param IdentityServiceInterface     $identityService
     * @param MediaResponseParserInterface $mediaResponseParser
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        MediaResponseParserInterface $mediaResponseParser
    ) {
        $this->identityService = $identityService;
        $this->mediaResponseParser = $mediaResponseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $entry)
    {
        $identity = $this->identityService->findOneOrCreate(
            (string) $entry['id'],
            PlentymarketsAdapter::NAME,
            Manufacturer::TYPE
        );

        $manufacturer = new Manufacturer();
        $manufacturer->setIdentifier($identity->getObjectIdentifier());
        $manufacturer->setName($entry['name']);

        if (!empty($entry['url'])) {
            $manufacturer->setLink($entry['url']);
        }

        if (!empty($entry['logo'])) {
            $media = $this->mediaResponseParser->parse([
                'mediaCategory' => MediaCategoryHelper::MANUFACTURER,
                'link' => $entry['logo'],
                'name' => $entry['name'],
                'alternateName' => $entry['name'],
            ]);

            $manufacturer->setLogoIdentifier($media->getIdentifier());

            $result[] = $media;
        }

        $result[] = $manufacturer;

        return $result;
    }
}
