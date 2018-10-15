<?php

namespace ShopwareAdapter\RequestGenerator\Product\ConfiguratorSet;

use SystemConnector\ConfigService\ConfigServiceInterface;
use SystemConnector\TransferObject\Product\Product;

class ConfiguratorSetRequestGenerator implements ConfiguratorSetRequestGeneratorInterface
{
    /**
     * @var ConfigServiceInterface
     */
    private $configService;

    public function __construct(ConfigServiceInterface $configService)
    {
        $this->configService = $configService;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Product $product)
    {
        $groups = [];
        foreach ($product->getVariantConfiguration() as $property) {
            $propertyName = $property->getName();

            $groups[$propertyName]['name'] = $propertyName;
            $groups[$propertyName]['position'] = $property->getPosition();

            foreach ($property->getValues() as $value) {
                $propertyValue = $value->getValue();

                $groups[$propertyName]['options'][$propertyValue]['name'] = $propertyValue;
                $groups[$propertyName]['options'][$propertyValue]['position'] = $value->getPosition();
            }
        }

        if (empty($groups)) {
            return [];
        }

        return [
            'name' => $product->getName(),
            'type' => (int) $this->configService->get('product_configurator_type', 0),
            'groups' => $groups,
        ];
    }
}
