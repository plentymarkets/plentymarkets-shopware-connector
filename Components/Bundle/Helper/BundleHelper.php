<?php

namespace PlentyConnector\Components\Bundle\Helper;

use SwagBundle\Models\Bundle as BundleModel;

class BundleHelper
{
    public function registerBundleModels()
    {
        if (class_exists(BundleModel::class)) {
            return;
        }
    }
}
