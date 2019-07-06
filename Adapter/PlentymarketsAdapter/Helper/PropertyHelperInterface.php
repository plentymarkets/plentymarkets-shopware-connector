<?php

namespace PlentymarketsAdapter\Helper;

interface PropertyHelperInterface
{
    /**
     * @param int   $propertyGroupId
     * @param array $propertyGroups
     *
     * @return array
     */
    public function getPropertyGroupNamesById($propertyGroupId, array $propertyGroups);

    /**
     * @param int   $propertyId
     * @param array $properties
     *
     * @return array
     */
    public function getPropertyNamesById($propertyId, array $properties);
}
