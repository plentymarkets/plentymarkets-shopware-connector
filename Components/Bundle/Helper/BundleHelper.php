<?php

namespace PlentyConnector\Components\Bundle\Helper;

use Enlight_Plugin_PluginManager;
use SwagBundle\Models\Bundle as BundleModel;

/**
 * Class BundleHelper
 */
class BundleHelper
{
    /**
     * @var Enlight_Plugin_PluginManager
     */
    private $pluginManager;

    /**
     * BundleHelper constructor.
     *
     * @param Enlight_Plugin_PluginManager $pluginManager
     */
    public function __construct(Enlight_Plugin_PluginManager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    public function registerBundleModels()
    {
        if (class_exists(BundleModel::class)) {
            return;
        }
    }
}
