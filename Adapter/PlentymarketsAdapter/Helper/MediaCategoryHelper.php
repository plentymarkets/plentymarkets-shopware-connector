<?php

namespace PlentymarketsAdapter\Helper;

/**
 * Class MediaCategoryHelper.
 */
class MediaCategoryHelper
{
    const MANUFACTURER = 1;
    const PRODUCT = 2;
    const CATEGORY = 3;

    public function getCategories()
    {
        return [
            $this::MANUFACTURER => [
                'id'   => $this::MANUFACTURER,
                'name' => 'Manufacturer',
            ],
            $this::PRODUCT => [
                'id'   => $this::PRODUCT,
                'name' => 'Products',
            ],
            $this::CATEGORY => [
                'id'   => $this::CATEGORY,
                'name' => 'Categories',
            ],
        ];
    }
}
