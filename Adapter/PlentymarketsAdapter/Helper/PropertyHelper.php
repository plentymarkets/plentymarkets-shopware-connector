<?php

namespace PlentymarketsAdapter\Helper;

class PropertyHelper implements PropertyHelperInterface
{
    /**
     * @param int $propertyGroupId
     *
     * @return array
     */
    public function getPropertyGroupNamesById($propertyGroupId, array $propertyGroups)
    {
        if (empty($propertyGroups)) {
            return [];
        }

        $propertyGroup = array_values(array_filter($propertyGroups, function (array $propertyGroup) use ($propertyGroupId) {
            return $propertyGroupId === $propertyGroup['id'];
        }))[0];

        if (!empty($propertyGroup['names'])) {
            return $propertyGroup['names'];
        }

        return [];
    }

    /**
     * @param int $propertyId
     *
     * @return array
     */
    public function getPropertyNamesById($propertyId, array $properties)
    {
        if (empty($properties)) {
            return [];
        }

        $property = array_values(array_filter($properties, function (array $property) use ($propertyId) {
            return $propertyId === $property['id'];
        }))[0];

        if (!empty($property['names'])) {
            return $property['names'];
        }

        return [];
    }
}
