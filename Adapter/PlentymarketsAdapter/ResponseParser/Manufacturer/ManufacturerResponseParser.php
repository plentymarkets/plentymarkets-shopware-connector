<?php

namespace PlentymarketsAdapter\ResponseParser\Manufacturer;

use Exception;
use PlentymarketsAdapter\Helper\MediaCategoryHelper;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Media\MediaResponseParserInterface;
use Psr\Log\LoggerInterface;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\Country\Country;
use SystemConnector\TransferObject\Manufacturer\Manufacturer;
use SystemConnector\ValueObject\Attribute\Attribute;

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
    public function parse(array $entry): array
    {
        $identity = $this->identityService->findOneOrCreate(
            (string) $entry['id'],
            PlentymarketsAdapter::NAME,
            Manufacturer::TYPE
        );

        $manufacturer = new Manufacturer();
        $manufacturer->setIdentifier($identity->getObjectIdentifier());
        $manufacturer->setName($entry['name']);

        $countryIdentity = $this->identityService->findOneBy([
            'adapterIdentifier' => (string) $entry['countryId'],
            'objectType' => Country::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        $additionalValues = [
            'street' => $entry['street'],
            'houseNo' => $entry['houseNo'],
            'postcode' => $entry['postcode'],
            'town' => $entry['town'],
            'phoneNumber' => $entry['phoneNumber'],
            'faxNumber' => $entry['faxNumber'],
            'email' => $entry['email'],
            'comment' => $entry['comment'],
        ];

        if (null !== $countryIdentity) {
            $additionalValues['countryIdentifier'] = $countryIdentity->getObjectIdentifier();
        }

        $manufacturer->setAttributes($this->setAttributes($additionalValues));

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

    /**
     * @param array $additionalValues
     *
     * @return Attribute[]
     */
    private function setAttributes(array $additionalValues): array
    {
        $attributes = [];

        foreach ($additionalValues as $key => $value) {
            $attribute = new Attribute();
            $attribute->setKey($key);
            $attribute->setValue((string) $value);

            $attributes[] = $attribute;
        }

        return $attributes;
    }
}
