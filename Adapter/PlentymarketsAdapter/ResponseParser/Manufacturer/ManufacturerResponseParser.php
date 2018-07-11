<?php

namespace PlentymarketsAdapter\ResponseParser\Manufacturer;

use Exception;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;
use PlentymarketsAdapter\Helper\MediaCategoryHelper;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Media\MediaResponseParserInterface;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ManufacturerResponseParser constructor.
     *
     * @param IdentityServiceInterface     $identityService
     * @param MediaResponseParserInterface $mediaResponseParser
     * @param LoggerInterface              $logger
     */
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
                    'link' => $entry['logo'],
                    'name' => $entry['name'],
                    'id' => $entry['id'],
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
