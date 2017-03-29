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
 * @copyright Copyright (c) 2013, plentymarkets GmbH (http://www.plentymarkets.com)
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */

/**
 * Helperfuncttion for the item sync.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportItemHelper
{
    /**
     * @var int
     */
    protected static $numbersCreated = 0;

    /**
     * Checks whether the item number is existant
     *
     * @param string $number
     *
     * @return bool
     */
    public static function isNumberExistant($number)
    {
        $filter = [
            'number' => $number,
        ];

        $detail = Shopware()->Models()
            ->getRepository('Shopware\Models\Article\Detail')
            ->findOneBy($filter);

        return !empty($detail);
    }

    /**
     * Checks whether the item number is from the given item id
     *
     * @param string $number
     * @param int $id
     *
     * @return bool
     */
    public static function isNumberExistantItem($number, $id = null)
    {
        $filter = [
            'number' => $number,
        ];

        if ($id) {
            $filter['articleId'] = $id;
        }

        $detail = Shopware()->Models()
            ->getRepository('Shopware\Models\Article\Detail')
            ->findOneBy($filter);

        return !empty($detail);
    }

    /**
     * Checks whether the item number is from the given detail
     *
     * @param string $number
     * @param int $id
     *
     * @return bool
     */
    public static function isNumberExistantVariant($number, $id = null)
    {
        $filter = [
            'number' => $number,
        ];

        if ($id) {
            $filter['id'] = $id;
        }

        $detail = Shopware()->Models()
            ->getRepository('Shopware\Models\Article\Detail')
            ->findOneBy($filter);

        return !empty($detail);
    }

    /**
     * Returns a generated item number
     *
     * @return string
     */
    public static function getItemNumber()
    {
        $prefix = Shopware()->Config()->backendAutoOrderNumberPrefix;

        $sql = "SELECT number FROM s_order_number WHERE name = 'articleordernumber'";
        $number = Shopware()->Db()->fetchOne($sql);
        $number += self::$numbersCreated;

        do {
            ++$number;
            ++self::$numbersCreated;

            $sql = 'SELECT id FROM s_articles_details WHERE ordernumber LIKE ?';
            $hit = Shopware()->Db()->fetchOne($sql, $prefix . $number);
        } while ($hit);

        Shopware()->Db()->query("UPDATE s_order_number SET number = ? WHERE name = 'articleordernumber'", [
            $number,
        ]);

        return $prefix . $number;
    }

    /**
     * Returns a usable item number
     *
     * @param string $number
     *
     * @return string
     */
    public static function getUsableNumber($number)
    {
        if (!empty($number) && !self::isNumberExistant($number)) {
            return $number;
        }

        return self::getItemNumber();
    }

    /**
     * Checks whether the number is valid
     *
     * @param string $number
     *
     * @return bool
     */
    public static function isNumberValid($number)
    {
        if (strlen($number) < 3 || strlen($number) > 40) {
            return false;
        }

        if (version_compare(Shopware::VERSION, '5.0.2', '>=')) {
            $regex = '/[^a-zA-Z0-9\-\_\.]/';
        } else {
            $regex = '/[^a-zA-Z0-9\.\-_ \/]/';
        }

        if (preg_match($regex, $number)) {
            return false;
        }

        return true;
    }
}
