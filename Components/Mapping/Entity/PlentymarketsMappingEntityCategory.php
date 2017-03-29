<?php
/**
 * plentymarkets shopware connector
 * Copyright © 2013 plentymarkets GmbH
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License, supplemented by an additional
 * permission, and of our proprietary license can be found
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "plentymarkets" is a registered trademark of plentymarkets GmbH.
 * "shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, titles and interests in the
 * above trademarks remain entirely with the trademark owners.
 *
 * @copyright  Copyright (c) 2013, plentymarkets GmbH (http://www.plentymarkets.com)
 * @author     Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */

/**
 * PlentymarketsMappingEntityCategory provides the actual category mapping functionality.
 * Like the other mapping entities this class is called in PlentymarketsMappingController. This entity
 * inherits the most methods from the entity class PlentymarketsMappingEntityAbstract.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsMappingEntityCategory extends PlentymarketsMappingEntityAbstract
{
    /**
     * @var string
     */
    const DELIMITER = ';';

    /**
     * Returns the identifier
     *
     * @param int $categoryId
     * @param int $storeOrShopId
     *
     * @return string
     */
    public static function getIdentifier($categoryId, $storeOrShopId)
    {
        return $categoryId . self::DELIMITER . $storeOrShopId;
    }

    /**
     * Returns the plentymarkets category id
     *
     * @param int $categoryId
     * @param int $shopId
     *
     * @return int
     */
    public static function getCategoryByShopwareID($categoryId, $shopId)
    {
        $category = PlentymarketsMappingController::getCategoryByShopwareID(
            self::getIdentifier($categoryId, $shopId)
        );
        $parts = explode(self::DELIMITER, $category);

        return (int) $parts[0];
    }

    /**
     * Returns the shopware category id
     *
     * @param int $categoryId
     * @param int $storeId
     *
     * @return int
     */
    public static function getCategoryByPlentyID($categoryId, $storeId)
    {
        $category = PlentymarketsMappingController::getCategoryByPlentyID(
            self::getIdentifier($categoryId, $storeId)
        );
        $parts = explode(self::DELIMITER, $category);

        return (int) $parts[0];
    }

    /**
     * Adds a category mapping
     *
     * @param int $shopwareCategoryId
     * @param int $shopId
     * @param int $plentyCategoryId
     * @param int $storeId
     */
    public static function addCategory($shopwareCategoryId, $shopId, $plentyCategoryId, $storeId)
    {
        PlentymarketsMappingController::addCategory(
            self::getIdentifier($shopwareCategoryId, $shopId),
            self::getIdentifier($plentyCategoryId, $storeId)
        );
    }

    /**
     * Returns the name of the database table
     *
     * @return string
     */
    protected function getName()
    {
        return 'plenty_mapping_category';
    }
}
