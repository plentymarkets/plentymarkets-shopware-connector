<?php

namespace PlentymarketsAdapter\ResponseParser\Manufacturer;

use Exception;
use PlentymarketsAdapter\Helper\MediaCategoryHelper;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Media\MediaResponseParserInterface;
use Psr\Log\LoggerInterface;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\Manufacturer\Manufacturer;

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
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        IdentityServiceInterface $identityService,
        MediaResponseParserInterface $mediaResponseParser,
        LoggerInterface $logger
    ) {
        $this->identityService = $identityService;
        $this->mediaResponseParser = $mediaResponseParser;
        $this->logger = $logger;
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

        $result = [];

        if (!empty($entry['logo'])) {
            try {
                $media = $this->mediaResponseParser->parse([
                    'mediaCategory' => MediaCategoryHelper::MANUFACTURER,
                    'id' => $entry['id'],
                    'link' => $entry['logo'],
                    'name' => $entry['name'],
                    'alternateName' => $entry['name'],
                ]);

                $manufacturer->setLogoIdentifier($media->getIdentifier());

                $result[] = $media;
            } catch (Exception $exception) {
                $this->logger->notice('error while processing manufacturer logo', [
                    'name' => $entry['name'],
                    'url' => $entry['logo'],
                ]);
            }
        }

        $result[] = $manufacturer;

        return $result;
    }
}
