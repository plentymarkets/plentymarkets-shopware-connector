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
 * The class PlentymarketsExportContinuousController handles the continuous expots.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportContinuousController
{
    /**
     * PlentymarketsExportContinuousController object data.
     *
     * @var PlentymarketsExportContinuousController
     */
    protected static $Instance;

    /**
     * PlentymarketsConfig object data.
     *
     * @var PlentymarketsConfig
     */
    protected $Config;

    /**
     * Prepares config data and checks different conditions like finished mapping.
     */
    protected function __construct()
    {
        $this->Config = PlentymarketsConfig::getInstance();
    }

    /**
     * If an instance of PlentymarketsExportContinuousController exists, it returns this instance.
     * Else it creates a new instance of PlentymarketsExportController.
     *
     * @return PlentymarketsExportContinuousController
     */
    public static function getInstance()
    {
        if (!self::$Instance instanceof self) {
            self::$Instance = new self();
        }

        return self::$Instance;
    }

    /**
     * Runs an export for the given entity.
     *
     * @param string $entity
     */
    public function run($entity)
    {
        $class = sprintf('PlentymarketsExportContinuousController%s', $entity);
        $Controller = new $class();
        $Controller->run();
    }
}
