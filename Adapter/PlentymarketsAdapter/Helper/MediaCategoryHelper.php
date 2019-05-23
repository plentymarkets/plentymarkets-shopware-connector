<?php

namespace PlentymarketsAdapter\Helper;

class MediaCategoryHelper implements MediaCategoryHelperInterface
{
    const MANUFACTURER = 1;
    const PRODUCT = 2;
    const CATEGORY = 3;

    /**
     * @return array
     */
    public function getCategories() :array
    {
        return [
            $this::MANUFACTURER => [
                'id' => $this::MANUFACTURER,
                'name' => 'Manufacturer',
            ],
            $this::PRODUCT => [
                'id' => $this::PRODUCT,
                'name' => 'Products',
            ],
            $this::CATEGORY => [
                'id' => $this::CATEGORY,
                'name' => 'Categories',
            ],
        ];
    }
}
