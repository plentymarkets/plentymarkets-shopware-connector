<?php
/**
 * plentymarkets shopware connector
 * Copyright © 2013 plentymarkets GmbH.
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
 * Checks for items with non-existant main details.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsDataIntegrityCheckItemMainDetailLost implements PlentymarketsDataIntegrityCheckInterface
{
    /**
     * Returns the name of the check.
     *
     * @see PlentymarketsDataIntegrityCheckInterface::getName()
     */
    public function getName()
    {
        return 'ItemMainDetailLost';
    }

    /**
     * Checks whether the check is valid.
     *
     * @return bool
     */
    public function isValid()
    {
        return count($this->getInvalidData(0, 1)) == 0;
    }

    /**
     * Returns a page of invalid data.
     *
     * @param int $start
     * @param int $offset
     *
     * @return array
     */
    public function getInvalidData($start, $offset)
    {
        return Shopware()->Db()->query('
			SELECT SQL_CALC_FOUND_ROWS a.id itemId, a.name, main_detail_id mainDetailId FROM s_articles a
				WHERE main_detail_id IS NULL OR main_detail_id NOT IN (SELECT id FROM s_articles_details)
				ORDER BY a.id
				LIMIT '.$start.', '.$offset.'
		')->fetchAll();
    }

    /**
     * Deletes a page of invalid data.
     *
     * @param int $start
     * @param int $offset
     */
    public function deleteInvalidData($start, $offset)
    {
        // Customer group
        $customerGroupKey = PlentymarketsConfig::getInstance()->getDefaultCustomerGroupKey();
        $customerGroupRepository = Shopware()->Models()->getRepository('Shopware\Models\Customer\Group');
        $customerGroups = $customerGroupRepository->findBy(['key' => $customerGroupKey]);
        $customerGroup = array_pop($customerGroups);

        foreach ($this->getInvalidData($start, $offset) as $data) {
            try {
                /** @var \Shopware\Models\Article\Article $Item */
                $Item = Shopware()->Models()->find('\Shopware\Models\Article\Article', $data['itemId']);

                $detail = new \Shopware\Models\Article\Detail();
                $detail->setArticle($Item);

                // The number will be changed by the sync process
                $detail->setNumber(PlentymarketsImportItemHelper::getItemNumber());

                $price = new Shopware\Models\Article\Price();
                $price->setFrom(1);
                $price->setPrice(1);
                $price->setPercent(0);
                $price->setArticle($Item);
                $price->setDetail($detail);
                $price->setCustomerGroup($customerGroup);

                $Item->setMainDetail($detail);

                Shopware()->Models()->persist($Item);
                Shopware()->Models()->remove($Item);
            } catch (Exception $E) {
            }
        }
        Shopware()->Models()->flush();
    }

    /**
     * Returns the fields to build an ext js model.
     *
     * @return array
     */
    public function getFields()
    {
        return [
            [
                'name'        => 'itemId',
                'description' => 'Artikel ID',
                'type'        => 'int',
            ],
            [
                'name'        => 'mainDetailId',
                'description' => 'Detail ID',
                'type'        => 'int',
            ],
            [
                'name'        => 'name',
                'description' => 'Bezeichnung',
                'type'        => 'string',
            ],
        ];
    }

    /**
     * Returns the total number of records.
     *
     * @return int
     */
    public function getTotal()
    {
        return (int) Shopware()->Db()->query('
			SELECT FOUND_ROWS()
		')->fetchColumn(0);
    }
}
