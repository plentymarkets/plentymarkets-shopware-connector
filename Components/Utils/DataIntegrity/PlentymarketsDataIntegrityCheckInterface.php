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
 * Data integrity check interface.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
interface PlentymarketsDataIntegrityCheckInterface
{
    /**
     * Returns the name of the check.
     */
    public function getName();

    /**
     * Returns a page of invalid data.
     *
     * @param int $start
     * @param int $offset
     */
    public function getInvalidData($start, $offset);

    /**
     * Deletes a page of invalid data.
     *
     * @param int $start
     * @param int $offset
     */
    public function deleteInvalidData($start, $offset);

    /**
     * Returns the total number of records.
     */
    public function getTotal();

    /**
     * Returns the fields to build an ext js model.
     */
    public function getFields();

    /**
     * Checks whether the check is valid.
     */
    public function isValid();
}
