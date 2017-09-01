<?php

namespace ShopwareAdapter\RequestGenerator\Product\ConfiguratorSet;

use PlentyConnector\Connector\ConfigService\ConfigServiceInterface;
use PlentyConnector\Connector\TransferObject\Product\Product;

/**
 * Class ConfiguratorSetRequestGenerator
 */
class ConfiguratorSetRequestGenerator implements ConfiguratorSetRequestGeneratorInterface
{
    /**
     * @var ConfigServiceInterface
     */
    private $config;

    /**
     * ConfiguratorSetRequestGenerator constructor.
     *
     * @param ConfigServiceInterface $config
     */
    public function __construct(ConfigServiceInterface $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Product $product)
    {
        if (empty($product->getVariations())) {
            return [];
        }

        $groups = [];
        foreach ($product->getVariations() as $variation) {
            if (empty($variation->getPrices())) {
                continue;
            }

            $properties = $variation->getProperties();

            foreach ($properties as $property) {
                $propertyName = $property->getName();

                $groups[$propertyName]['name'] = $propertyName;

                foreach ($property->getValues() as $value) {
                    $propertyValue = $value->getValue();

                    $groups[$propertyName]['options'][$propertyValue]['name'] = $propertyValue;
                }
            }
        }

        if (empty($groups)) {
            return [];
        }

        return [
            'name' => $product->getName(),
            'type' => (int) $this->config->get('product_configurator_type', 0),
            'groups' => $groups,
        ];
    }
}
