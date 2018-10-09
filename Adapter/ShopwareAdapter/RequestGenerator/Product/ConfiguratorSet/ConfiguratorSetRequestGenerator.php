<?php

namespace ShopwareAdapter\RequestGenerator\Product\ConfiguratorSet;

use PlentyConnector\Connector\ConfigService\ConfigServiceInterface;
use PlentyConnector\Connector\TransferObject\Product\Product;

class ConfiguratorSetRequestGenerator implements ConfiguratorSetRequestGeneratorInterface
{
    /**
     * @var ConfigServiceInterface
     */
    private $config;

    public function __construct(ConfigServiceInterface $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Product $product)
    {
        $groups = [];
        $plentyConfiguratorType = 'default';

        foreach ($product->getVariantConfiguration() as $property) {
            $propertyName = $property->getName();

            $groups[$propertyName]['name'] = $propertyName;
            $groups[$propertyName]['position'] = $property->getPosition();

            foreach ($property->getValues() as $value) {
                $propertyValue = $value->getValue();

                $groups[$propertyName]['options'][$propertyValue]['name'] = $propertyValue;
                $groups[$propertyName]['options'][$propertyValue]['position'] = $value->getPosition();
            }

            if ($plentyConfiguratorType !== 'default' && $plentyConfiguratorType !== $property->getType()) {
                $plentyConfiguratorType = 'default';
                continue;
            }

            $plentyConfiguratorType = $property->getType();
        }

        if (empty($groups)) {
            return [];
        }

        $type = (int) $this->config->get('product_configurator_type', 0);

        switch ($plentyConfiguratorType) {
            case 'box':
                $type = 0;
                break;
            case 'dropdown':
                $type = 1;
                break;
            case 'image':
                $type = 2;
                break;
        }

        return [
            'name' => $product->getName(),
            'type' => $type,
            'groups' => $groups,
        ];
    }
}
