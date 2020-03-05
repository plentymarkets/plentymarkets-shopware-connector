<?php

namespace PlentymarketsAdapter\Helper;

interface PropertyHelperInterface
{
    /**
     * @param int $propertyGroupId
     *
     * @return array
     */
    public function getPropertyGroupNamesById($propertyGroupId, array $propertyGroups);

    /**
     * @param int $propertyId
     *
     * @return array
     */
    public function getPropertyNamesById($propertyId, array $properties);
}
