<?php

namespace PlentyConnector\Components\Bundle\Plentymarkets;

use PlentyConnector\Components\Bundle\TransferObject\Bundle;
use PlentyConnector\Components\Bundle\TransferObject\BundleProduct\BundleProduct;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Product\Product;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Product\ProductResponseParserInterface;

/**
 * Class BundleResponseParser
 */
class BundleResponseParser implements ProductResponseParserInterface
{
    /**
     * @var ProductResponseParserInterface
     */
    private $parentResponseParser;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * BundleResponseParser constructor.
     *
     * @param ProductResponseParserInterface $parentResponseParser
     * @param IdentityServiceInterface       $identityService
     * @param ClientInterface                $client
     */
    public function __construct(
        ProductResponseParserInterface $parentResponseParser,
        IdentityServiceInterface $identityService,
        ClientInterface $client
    ) {
        $this->parentResponseParser = $parentResponseParser;
        $this->identityService = $identityService;
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $product)
    {
        $result = $this->parentResponseParser->parse($product);

        $mainVariation = $this->getMainVariation($product['variations']);

        if ($mainVariation['bundleType'] !== 'bundle') {
            return $result;
        }

        $matchedProduct = $this->getProduct($result, $product);

        if (null === $matchedProduct) {
            return $result;
        }

        $identity = $this->identityService->findOneOrCreate(
            (string) $product['id'],
            PlentymarketsAdapter::NAME,
            Bundle::TYPE
        );

        $bundleProducts = $this->getBundleProducts($mainVariation);

        if (empty($bundleProducts)) {
            return $result;
        }

        $bundle = new Bundle();
        $bundle->setIdentifier($identity->getObjectIdentifier());
        $bundle->setProduct($matchedProduct);
        $bundle->setBundleProducts($bundleProducts);

        $result[] = $bundle;

        return $result;
    }

    /**
     * @param array $variations
     *
     * @return array
     */
    private function getMainVariation(array $variations)
    {
        $mainVariation = array_filter($variations, function ($varation) {
            return $varation['isMain'] === true;
        });

        if (empty($mainVariation)) {
            throw new \InvalidArgumentException('product without main variaton');
        }

        return array_shift($mainVariation);
    }

    /**
     * @param array $elements
     */
    private function addProductNumberToResponse(array &$elements)
    {
        $ids = implode(',', array_column($elements, 'componentVariationId'));
        $variations = $this->client->request('GET', 'items/variations', ['id' => $ids]);

        foreach ($elements as &$element) {
            $matchedVariations = array_filter($variations, function (array $variation) use ($element) {
                return (int) $element['componentVariationId'] === (int) $variation['id'];
            });

            if (empty($matchedVariations)) {
                continue;
            }

            $variation = array_shift($matchedVariations);

            $element['number'] = $variation['number'];
        }
    }

    /**
     * @param array $mainVariation
     *
     * @return BundleProduct[]
     */
    private function getBundleProducts(array $mainVariation)
    {
        $url = 'items/' . $mainVariation['itemId'] . '/variations/' . $mainVariation['id'] . '/variation_bundles';
        $elements = $this->client->request('GET', $url);

        $this->addProductNumberToResponse($elements);

        $result = [];
        foreach ($elements as $element) {
            $bundleProduct = new BundleProduct();
            $bundleProduct->setAmount((float) $element['componentQuantity']);
            $bundleProduct->setNumber($element['number']);

            $result[] = $bundleProduct;
        }

        return $result;
    }

    /**
     * @param TransferObjectInterface[] $result
     * @param array                     $product
     *
     * @return null|Product
     */
    private function getProduct(array $result, array $product)
    {
        $matchedProducts = array_filter($result, function (TransferObjectInterface $object) use ($product) {
            if (!($object instanceof Product)) {
                return false;
            }

            $productIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => $object->getIdentifier(),
                'objectType' => Product::TYPE,
                'adapterName' => PlentymarketsAdapter::NAME,
            ]);

            if (null === $productIdentity) {
                return false;
            }

            if ($productIdentity->getAdapterIdentifier() !== (string) $product['id']) {
                return false;
            }

            return true;
        });

        if (empty($matchedProducts)) {
            return null;
        }

        return array_shift($matchedProducts);
    }
}
