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

define('PY_BASE', __DIR__ . DIRECTORY_SEPARATOR);
define('PY_COMPONENTS', PY_BASE . 'Components' . DIRECTORY_SEPARATOR);
define('PY_SOAP', PY_COMPONENTS . 'Soap' . DIRECTORY_SEPARATOR);
define('PY_CONTROLLERS', PY_BASE . 'Controllers' . DIRECTORY_SEPARATOR);

require_once PY_COMPONENTS . 'Utils/PlentymarketsAutoLoader.php';
PlentymarketsAutoLoader::register();

define('IMPORT_ITEM_IMAGE_SYNC', 1);
define('IMPORT_ITEM_IMAGE_NO_SYNC', 0);

define('IMPORT_ITEM_CATEGORY_SYNC', 1);
define('IMPORT_ITEM_CATEGORY_NO_SYNC', 0);

define('IMPORT_ITEM_NUMBER', 1);
define('IMPORT_ITEM_NUMBER_NO', 0);

define('IMPORT_ITEM_BUNDLE_HEAD', 1);
define('IMPORT_ITEM_BUNDLE_HEAD_NO', 0);

define('EXPORT_ORDER_ITEM_TEXT_SYNC', 1);
define('EXPORT_ORDER_ITEM_TEXT_SYNC_NO', 0);

define('MOP_DEBIT', 3);
define('MOP_SHOPGATE', 20);
define('MOP_AMAZON_PAYMENT', 40);
define('MOP_KLARNA', 1401);
define('MOP_KLARNACREDIT', 1402);

/**
 * Shortcut for PlentymarketsConfig::getInstance()
 *
 * @return PlentymarketsConfig
 */
function PyConf()
{
	return PlentymarketsConfig::getInstance();
}

/**
 * Shortcut for PlentymarketsLogger::getInstance()
 *
 * @return PlentymarketsLogger
 */
function PyLog()
{
	return PlentymarketsLogger::getInstance();
}

/**
 * Shortcut for PlentymarketsStatus::getInstance()
 *
 * @return PlentymarketsStatus
 */
function PyStatus()
{
	return PlentymarketsStatus::getInstance();
}

/**
 * This class is called first when starting the plentymarkets plugin. It initializes and cleans all important data.
 * It also provides cronjob functionality for an initial execution of the plentymarkets plugin.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class Shopware_Plugins_Backend_PlentyConnector_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Installs the plugin
     *
     * @return bool
     */
    public function install()
    {
		// Check for the current versions
		if (!$this->assertRequiredPluginsPresent(array('Cron')))
		{
			return array(
				'success' => false,
				'message' => 'Bitte installieren und aktivieren Sie das Cron-Plugin'
			);
		}

    	if (!$this->assertVersionGreaterThen('4.1'))
    	{
    		return array(
				'success' => false,
				'message' => 'Das plentymarkets-Plugin benötigt min. shopware 4.1'
			);
    	}

        $this->createDatabase();
        $this->createEvents();
        $this->createMenu();
        $this->registerCronjobs();

        //
        PlentymarketsConfig::getInstance()->setConnectorVersion($this->getVersion());

        return true;
    }

    /**
     * Update plugin method
     *
     * @see Shopware_Components_Plugin_Bootstrap::update()
     */
    public function update($version)
    {
    	$Logger = PlentymarketsLogger::getInstance();
    	$Logger->message(PlentymarketsLogger::PREFIX_UPDATE, $version . ' →  ' . $this->getVersion());

    	if ($version == '1.4.3')
    	{
			try
			{
				// Drop unused columns from the log
				Shopware()->Db()->exec("
					ALTER TABLE `plenty_log`
						DROP `request`,
						DROP `response`;
				");
			}
			catch (Exception $E)
			{
				$Logger->message(PlentymarketsLogger::PREFIX_UPDATE, 'ALTER TABLE `plenty_log` (drop unused columns from the log) already carried out');
			}
    	}

		if (version_compare($version, '1.4.4') !== 1)
		{
			try
			{
				Shopware()->Db()->exec("
					CREATE TABLE `plenty_mapping_customer_billing_address` (
					  `shopwareID` int(11) unsigned NOT NULL,
					  `plentyID` int(11) unsigned NOT NULL,
					  PRIMARY KEY (`shopwareID`,`plentyID`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8
				");

				$Logger->message(PlentymarketsLogger::PREFIX_UPDATE, 'CREATE TABLE `plenty_mapping_customer_billing_address` done');
			}
			catch (Exception $E)
			{
				$Logger->message(PlentymarketsLogger::PREFIX_UPDATE, 'CREATE TABLE `plenty_mapping_customer_billing_address` already carried out');
			}

			Shopware()->Db()->exec("
				TRUNCATE TABLE `plenty_mapping_customer`
			");

			$Logger->message(PlentymarketsLogger::PREFIX_UPDATE, 'TRUNCATE TABLE `plenty_mapping_customer` done');

			Shopware()->Db()->exec("
				DELETE FROM `plenty_config` WHERE `key` LIKE 'CustomerExport%'
			");

			$Logger->message(PlentymarketsLogger::PREFIX_UPDATE, 'DELETE FROM `plenty_config` done');
		}

		if (version_compare($version, '1.4.5') !== 1)
		{
			try
			{
				$this->addMappingCleanupCronEvent();

				$Logger->message(PlentymarketsLogger::PREFIX_UPDATE, 'addMappingCleanupCronEvent done');
			}
			catch (Exception $E)
			{
				$Logger->message(PlentymarketsLogger::PREFIX_UPDATE, 'addMappingCleanupCronEvent already carried out');
			}
		}

		if (version_compare($version, '1.4.7') !== 1)
		{
			if (PlentymarketsConfig::getInstance()->getItemExportStatus() == 'success')
			{
				PlentymarketsConfig::getInstance()->setItemCrossSellingExportStatus('success');
				PlentymarketsConfig::getInstance()->setItemCrossSellingExportTimestampStart(time());
				PlentymarketsConfig::getInstance()->setItemCrossSellingExportTimestampFinished(time());

				$Logger->message(PlentymarketsLogger::PREFIX_UPDATE, 'Item cross selling export marked as done');
			}
		}

		if (version_compare($version, '1.4.8') !== 1)
		{
			$this->addItemImportStackCronEvent();

			$Logger->message(PlentymarketsLogger::PREFIX_UPDATE, 'addItemImportStackCronEvent done');

			try
			{
				Shopware()->Db()->exec("
					CREATE TABLE `plenty_stack_item` (
					  `itemId` int(11) unsigned NOT NULL,
					  `timestamp` int(10) unsigned NOT NULL,
					  `storeIds` text NOT NULL,
					  PRIMARY KEY (`itemId`),
					  KEY `timestamp` (`timestamp`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
				");

				$Logger->message(PlentymarketsLogger::PREFIX_UPDATE, 'CREATE TABLE `plenty_stack_item` done');
			}
			catch (Exception $E)
			{
				$Logger->message(PlentymarketsLogger::PREFIX_UPDATE, 'CREATE TABLE `plenty_stack_item` already carried out');
			}

			try
			{
				Shopware()->Db()->exec("
					UPDATE plenty_config
						SET `key` = 'ImportItemStackLastUpdateTimestamp'
						WHERE `key` = 'ImportItemLastUpdateTimestamp'
				");

				$Logger->message(PlentymarketsLogger::PREFIX_UPDATE, 'UPDATE plenty_config (ImportItemStackLastUpdateTimestamp) done');
			}
			catch (Exception $E)
			{
				$Logger->message(PlentymarketsLogger::PREFIX_UPDATE, 'UPDATE plenty_config (ImportItemStackLastUpdateTimestamp) failed');
			}
		}

		if (version_compare($version, '1.4.12') !== 1)
		{
			try
			{
				Shopware()->Db()->exec("
					ALTER TABLE `plenty_log` ADD `code` INT  UNSIGNED  NULL  DEFAULT NULL  AFTER `message`;
				");

				$Logger->message(PlentymarketsLogger::PREFIX_UPDATE, 'ALTER TABLE `plenty_log` ADD `code` done');
			}
			catch (Exception $E)
			{
				$Logger->message(PlentymarketsLogger::PREFIX_UPDATE, 'ALTER TABLE `plenty_log` ADD `code` already carried out');
			}
		}

		if (version_compare($version, '1.4.14') !== 1)
		{
			$this->addLogCleanupCronEvent();
		}

		if (version_compare($version, '1.4.18') !== 1)
		{
			$this->addItemAssociateUpdateCronEvent();
		}

		if (version_compare($version, '1.4.22') !== 1)
		{
			try
			{
				Shopware()->Db()->exec("
					CREATE TABLE `plenty_mapping_item_bundle` (
					  `shopwareID` int(11) unsigned NOT NULL,
					  `plentyID` int(11) unsigned NOT NULL,
					  PRIMARY KEY (`shopwareID`,`plentyID`),
					  UNIQUE KEY `plentyID` (`plentyID`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
				");

				$Logger->message(PlentymarketsLogger::PREFIX_UPDATE, 'CREATE TABLE `plenty_mapping_item_bundle` done');
			}
			catch (Exception $E)
			{
				$Logger->message(PlentymarketsLogger::PREFIX_UPDATE, 'CREATE TABLE `plenty_mapping_item_bundle` failed');
			}

			$this->addItemBundleCronEvents();
		}

		if (version_compare($version, '1.6') !== 1)
		{
			try
			{
				PlentymarketsExportController::getInstance()->erase('ItemCategory');

				$Logger->message(PlentymarketsLogger::PREFIX_UPDATE, 'PlentymarketsExportController::getInstance()->erase(\'ItemCategory\') done');
			}
			catch (Exception $E)
			{
				$Logger->message(PlentymarketsLogger::PREFIX_UPDATE, 'PlentymarketsExportController::getInstance()->erase(\'ItemCategory\') failed');
			}

			try
			{
				Shopware()->Db()->exec("
					ALTER TABLE `plenty_mapping_category` CHANGE `shopwareID` `shopwareID` VARCHAR(255) NOT NULL DEFAULT '';
				");

				$Logger->message(PlentymarketsLogger::PREFIX_UPDATE, 'ALTER TABLE `plenty_mapping_category` done');
			}
			catch (Exception $E)
			{
				$Logger->message(PlentymarketsLogger::PREFIX_UPDATE, 'ALTER TABLE `plenty_mapping_category` failed');
			}


			PyConf()->erase('PlentymarketsVersionTimestamp');
		}

		//
		PlentymarketsConfig::getInstance()->setConnectorVersion($this->getVersion());

    	return true;
    }

	/**
	 * Uninstall plugin method
	 *
	 * @see Shopware_Components_Plugin_Bootstrap::uninstall()
	 */
	public function uninstall()
	{
		$tablesToDelete = array(
			'plenty_config',
			'plenty_log',
			'plenty_mapping_attribute_group',
			'plenty_mapping_attribute_option',
			'plenty_mapping_category',
			'plenty_mapping_country',
			'plenty_mapping_currency',
			'plenty_mapping_customer',
			'plenty_mapping_customer_billing_address',
			'plenty_mapping_customer_class',
			'plenty_mapping_item',
			'plenty_mapping_item_bundle',
			'plenty_mapping_item_variant',
			'plenty_mapping_measure_unit',
			'plenty_mapping_method_of_payment',
			'plenty_mapping_producer',
			'plenty_mapping_property',
			'plenty_mapping_property_group',
			'plenty_mapping_referrer',
			'plenty_mapping_shipping_profile',
			'plenty_mapping_shop',
			'plenty_mapping_vat',
			'plenty_order',
			'plenty_stack_item'
		);

		foreach ($tablesToDelete as $table)
		{
			Shopware()->Db()->query('
				DROP TABLE IF EXISTS `'. $table .'`
			');
		}

		return true;
	}

	/**
	 * Returns capabilities
	 *
	 * @see Shopware_Components_Plugin_Bootstrap::getCapabilities()
	 */
	public function getCapabilities()
	{
		return array(
			'uninstall' => true,
			'install' => true,
			'enable' => true,
			'update' => true
		);
	}

	/**
	 * Creates all database tables
	 */
	protected function createDatabase()
	{
		Shopware()->Db()->exec("
			CREATE TABLE `plenty_config` (
			  `key` varchar(125) NOT NULL DEFAULT '',
			  `value` text NOT NULL,
			  PRIMARY KEY (`key`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  		");

		Shopware()->Db()->exec("
			INSERT INTO `plenty_config` (`key`, `value`)
			VALUES
				('ApiWsdl','http://www.plentymarkets.eu/'),
				('ItemMeasureUnitsSerialized','a:52:{s:3:\"C62\";a:2:{s:2:\"id\";s:3:\"C62\";s:4:\"name\";s:12:\"Stück (Stk)\";}s:3:\"KGM\";a:2:{s:2:\"id\";s:3:\"KGM\";s:4:\"name\";s:14:\"Kilogramm (kg)\";}s:3:\"GRM\";a:2:{s:2:\"id\";s:3:\"GRM\";s:4:\"name\";s:9:\"Gramm (g)\";}s:3:\"MGM\";a:2:{s:2:\"id\";s:3:\"MGM\";s:4:\"name\";s:15:\"Milligramm (mg)\";}s:3:\"LTR\";a:2:{s:2:\"id\";s:3:\"LTR\";s:4:\"name\";s:9:\"Liter (l)\";}s:3:\"DPC\";a:2:{s:2:\"id\";s:3:\"DPC\";s:4:\"name\";s:9:\"12 Stück\";}s:2:\"OP\";a:2:{s:2:\"id\";s:2:\"OP\";s:4:\"name\";s:8:\"2er Pack\";}s:2:\"BL\";a:2:{s:2:\"id\";s:2:\"BL\";s:4:\"name\";s:6:\"Ballen\";}s:2:\"DI\";a:2:{s:2:\"id\";s:2:\"DI\";s:4:\"name\";s:9:\"Behälter\";}s:2:\"BG\";a:2:{s:2:\"id\";s:2:\"BG\";s:4:\"name\";s:6:\"Beutel\";}s:2:\"ST\";a:2:{s:2:\"id\";s:2:\"ST\";s:4:\"name\";s:5:\"Blatt\";}s:3:\"D64\";a:2:{s:2:\"id\";s:3:\"D64\";s:4:\"name\";s:5:\"Block\";}s:2:\"PD\";a:2:{s:2:\"id\";s:2:\"PD\";s:4:\"name\";s:5:\"Block\";}s:2:\"QR\";a:2:{s:2:\"id\";s:2:\"QR\";s:4:\"name\";s:5:\"Bogen\";}s:2:\"BX\";a:2:{s:2:\"id\";s:2:\"BX\";s:4:\"name\";s:3:\"Box\";}s:2:\"CL\";a:2:{s:2:\"id\";s:2:\"CL\";s:4:\"name\";s:4:\"Bund\";}s:2:\"CH\";a:2:{s:2:\"id\";s:2:\"CH\";s:4:\"name\";s:9:\"Container\";}s:2:\"TN\";a:2:{s:2:\"id\";s:2:\"TN\";s:4:\"name\";s:4:\"Dose\";}s:2:\"CA\";a:2:{s:2:\"id\";s:2:\"CA\";s:4:\"name\";s:12:\"Dose/Büchse\";}s:3:\"DZN\";a:2:{s:2:\"id\";s:3:\"DZN\";s:4:\"name\";s:7:\"Dutzend\";}s:2:\"BJ\";a:2:{s:2:\"id\";s:2:\"BJ\";s:4:\"name\";s:5:\"Eimer\";}s:2:\"CS\";a:2:{s:2:\"id\";s:2:\"CS\";s:4:\"name\";s:4:\"Etui\";}s:2:\"Z3\";a:2:{s:2:\"id\";s:2:\"Z3\";s:4:\"name\";s:4:\"Fass\";}s:2:\"BO\";a:2:{s:2:\"id\";s:2:\"BO\";s:4:\"name\";s:7:\"Flasche\";}s:3:\"OZA\";a:2:{s:2:\"id\";s:3:\"OZA\";s:4:\"name\";s:21:\"Flüssigunze (fl.oz.)\";}s:2:\"JR\";a:2:{s:2:\"id\";s:2:\"JR\";s:4:\"name\";s:12:\"Glas/Gefäß\";}s:2:\"CG\";a:2:{s:2:\"id\";s:2:\"CG\";s:4:\"name\";s:6:\"Karton\";}s:2:\"CT\";a:2:{s:2:\"id\";s:2:\"CT\";s:4:\"name\";s:9:\"Kartonage\";}s:2:\"KT\";a:2:{s:2:\"id\";s:2:\"KT\";s:4:\"name\";s:3:\"Kit\";}s:2:\"AA\";a:2:{s:2:\"id\";s:2:\"AA\";s:4:\"name\";s:7:\"Knäuel\";}s:3:\"MTR\";a:2:{s:2:\"id\";s:3:\"MTR\";s:4:\"name\";s:9:\"Meter (m)\";}s:3:\"MLT\";a:2:{s:2:\"id\";s:3:\"MLT\";s:4:\"name\";s:15:\"Milliliter (ml)\";}s:3:\"MMT\";a:2:{s:2:\"id\";s:3:\"MMT\";s:4:\"name\";s:15:\"Millimeter (mm)\";}s:2:\"PR\";a:2:{s:2:\"id\";s:2:\"PR\";s:4:\"name\";s:4:\"Paar\";}s:2:\"PA\";a:2:{s:2:\"id\";s:2:\"PA\";s:4:\"name\";s:9:\"Päckchen\";}s:2:\"PK\";a:2:{s:2:\"id\";s:2:\"PK\";s:4:\"name\";s:5:\"Paket\";}s:3:\"D97\";a:2:{s:2:\"id\";s:3:\"D97\";s:4:\"name\";s:7:\"Palette\";}s:3:\"MTK\";a:2:{s:2:\"id\";s:3:\"MTK\";s:4:\"name\";s:17:\"Quadratmeter (qm)\";}s:3:\"CMK\";a:2:{s:2:\"id\";s:3:\"CMK\";s:4:\"name\";s:17:\"Quadratzentimeter\";}s:3:\"MMK\";a:2:{s:2:\"id\";s:3:\"MMK\";s:4:\"name\";s:17:\"Quadratmillimeter\";}s:3:\"SCM\";a:2:{s:2:\"id\";s:3:\"SCM\";s:4:\"name\";s:34:\"Quadratzentimeter (kein Standard!)\";}s:3:\"SMM\";a:2:{s:2:\"id\";s:3:\"SMM\";s:4:\"name\";s:34:\"Quadratmillimeter (kein Standard!)\";}s:2:\"RO\";a:2:{s:2:\"id\";s:2:\"RO\";s:4:\"name\";s:5:\"Rolle\";}s:2:\"SA\";a:2:{s:2:\"id\";s:2:\"SA\";s:4:\"name\";s:4:\"Sack\";}s:3:\"SET\";a:2:{s:2:\"id\";s:3:\"SET\";s:4:\"name\";s:4:\"Satz\";}s:2:\"RL\";a:2:{s:2:\"id\";s:2:\"RL\";s:4:\"name\";s:5:\"Spule\";}s:2:\"EA\";a:2:{s:2:\"id\";s:2:\"EA\";s:4:\"name\";s:6:\"Stück\";}s:2:\"TU\";a:2:{s:2:\"id\";s:2:\"TU\";s:4:\"name\";s:9:\"Tube/Rohr\";}s:2:\"OZ\";a:2:{s:2:\"id\";s:2:\"OZ\";s:4:\"name\";s:10:\"Unze (oz.)\";}s:2:\"WE\";a:2:{s:2:\"id\";s:2:\"WE\";s:4:\"name\";s:17:\"Wascheinheit (WE)\";}s:3:\"CMT\";a:2:{s:2:\"id\";s:3:\"CMT\";s:4:\"name\";s:15:\"Zentimeter (cm)\";}s:3:\"INH\";a:2:{s:2:\"id\";s:3:\"INH\";s:4:\"name\";s:9:\"Zoll (in)\";}}'),
				('MiscCountriesSerialized','a:249:{i:1;a:5:{s:2:\"id\";i:1;s:4:\"name\";s:11:\"Deutschland\";s:10:\"iso_code_2\";s:2:\"DE\";s:10:\"iso_code_3\";s:3:\"DEU\";s:4:\"lang\";s:2:\"de\";}i:2;a:5:{s:2:\"id\";i:2;s:4:\"name\";s:11:\"Österreich\";s:10:\"iso_code_2\";s:2:\"AT\";s:10:\"iso_code_3\";s:3:\"AUT\";s:4:\"lang\";s:2:\"de\";}i:3;a:5:{s:2:\"id\";i:3;s:4:\"name\";s:7:\"Belgien\";s:10:\"iso_code_2\";s:2:\"BE\";s:10:\"iso_code_3\";s:3:\"BEL\";s:4:\"lang\";s:0:\"\";}i:4;a:5:{s:2:\"id\";i:4;s:4:\"name\";s:7:\"Schweiz\";s:10:\"iso_code_2\";s:2:\"CH\";s:10:\"iso_code_3\";s:3:\"CHE\";s:4:\"lang\";s:2:\"de\";}i:5;a:5:{s:2:\"id\";i:5;s:4:\"name\";s:6:\"Zypern\";s:10:\"iso_code_2\";s:2:\"CY\";s:10:\"iso_code_3\";s:3:\"CYP\";s:4:\"lang\";s:0:\"\";}i:6;a:5:{s:2:\"id\";i:6;s:4:\"name\";s:10:\"Tschechien\";s:10:\"iso_code_2\";s:2:\"CZ\";s:10:\"iso_code_3\";s:3:\"CZE\";s:4:\"lang\";s:0:\"\";}i:7;a:5:{s:2:\"id\";i:7;s:4:\"name\";s:9:\"Dänemark\";s:10:\"iso_code_2\";s:2:\"DK\";s:10:\"iso_code_3\";s:3:\"DNK\";s:4:\"lang\";s:0:\"\";}i:8;a:5:{s:2:\"id\";i:8;s:4:\"name\";s:7:\"Spanien\";s:10:\"iso_code_2\";s:2:\"ES\";s:10:\"iso_code_3\";s:3:\"ESP\";s:4:\"lang\";s:0:\"\";}i:9;a:5:{s:2:\"id\";i:9;s:4:\"name\";s:7:\"Estland\";s:10:\"iso_code_2\";s:2:\"EE\";s:10:\"iso_code_3\";s:3:\"EST\";s:4:\"lang\";s:0:\"\";}i:10;a:5:{s:2:\"id\";i:10;s:4:\"name\";s:10:\"Frankreich\";s:10:\"iso_code_2\";s:2:\"FR\";s:10:\"iso_code_3\";s:3:\"FRA\";s:4:\"lang\";s:0:\"\";}i:11;a:5:{s:2:\"id\";i:11;s:4:\"name\";s:8:\"Finnland\";s:10:\"iso_code_2\";s:2:\"FI\";s:10:\"iso_code_3\";s:3:\"FIN\";s:4:\"lang\";s:0:\"\";}i:12;a:5:{s:2:\"id\";i:12;s:4:\"name\";s:7:\"England\";s:10:\"iso_code_2\";s:2:\"GB\";s:10:\"iso_code_3\";s:3:\"GBR\";s:4:\"lang\";s:2:\"de\";}i:13;a:5:{s:2:\"id\";i:13;s:4:\"name\";s:12:\"Griechenland\";s:10:\"iso_code_2\";s:2:\"GR\";s:10:\"iso_code_3\";s:3:\"GRC\";s:4:\"lang\";s:0:\"\";}i:14;a:5:{s:2:\"id\";i:14;s:4:\"name\";s:6:\"Ungarn\";s:10:\"iso_code_2\";s:2:\"HU\";s:10:\"iso_code_3\";s:3:\"HUN\";s:4:\"lang\";s:0:\"\";}i:15;a:5:{s:2:\"id\";i:15;s:4:\"name\";s:6:\"Italia\";s:10:\"iso_code_2\";s:2:\"IT\";s:10:\"iso_code_3\";s:3:\"ITA\";s:4:\"lang\";s:0:\"\";}i:16;a:5:{s:2:\"id\";i:16;s:4:\"name\";s:6:\"Irland\";s:10:\"iso_code_2\";s:2:\"IE\";s:10:\"iso_code_3\";s:3:\"IRL\";s:4:\"lang\";s:0:\"\";}i:17;a:5:{s:2:\"id\";i:17;s:4:\"name\";s:9:\"Luxemburg\";s:10:\"iso_code_2\";s:2:\"LU\";s:10:\"iso_code_3\";s:3:\"LUX\";s:4:\"lang\";s:0:\"\";}i:18;a:5:{s:2:\"id\";i:18;s:4:\"name\";s:8:\"Lettland\";s:10:\"iso_code_2\";s:2:\"LV\";s:10:\"iso_code_3\";s:3:\"LVA\";s:4:\"lang\";s:0:\"\";}i:19;a:5:{s:2:\"id\";i:19;s:4:\"name\";s:5:\"Malta\";s:10:\"iso_code_2\";s:2:\"MT\";s:10:\"iso_code_3\";s:3:\"MLT\";s:4:\"lang\";s:0:\"\";}i:20;a:5:{s:2:\"id\";i:20;s:4:\"name\";s:8:\"Norwegen\";s:10:\"iso_code_2\";s:2:\"NO\";s:10:\"iso_code_3\";s:3:\"NOR\";s:4:\"lang\";s:0:\"\";}i:21;a:5:{s:2:\"id\";i:21;s:4:\"name\";s:11:\"Niederlande\";s:10:\"iso_code_2\";s:2:\"NL\";s:10:\"iso_code_3\";s:3:\"NLD\";s:4:\"lang\";s:0:\"\";}i:22;a:5:{s:2:\"id\";i:22;s:4:\"name\";s:8:\"Portugal\";s:10:\"iso_code_2\";s:2:\"PT\";s:10:\"iso_code_3\";s:3:\"PRT\";s:4:\"lang\";s:0:\"\";}i:23;a:5:{s:2:\"id\";i:23;s:4:\"name\";s:5:\"Polen\";s:10:\"iso_code_2\";s:2:\"PL\";s:10:\"iso_code_3\";s:3:\"POL\";s:4:\"lang\";s:0:\"\";}i:24;a:5:{s:2:\"id\";i:24;s:4:\"name\";s:8:\"Schweden\";s:10:\"iso_code_2\";s:2:\"SE\";s:10:\"iso_code_3\";s:3:\"SWE\";s:4:\"lang\";s:0:\"\";}i:25;a:5:{s:2:\"id\";i:25;s:4:\"name\";s:8:\"Singapur\";s:10:\"iso_code_2\";s:2:\"SG\";s:10:\"iso_code_3\";s:3:\"SGP\";s:4:\"lang\";s:0:\"\";}i:26;a:5:{s:2:\"id\";i:26;s:4:\"name\";s:20:\"Slowakische Republik\";s:10:\"iso_code_2\";s:2:\"SK\";s:10:\"iso_code_3\";s:3:\"SVK\";s:4:\"lang\";s:0:\"\";}i:27;a:5:{s:2:\"id\";i:27;s:4:\"name\";s:9:\"Slowenien\";s:10:\"iso_code_2\";s:2:\"SI\";s:10:\"iso_code_3\";s:3:\"SVN\";s:4:\"lang\";s:0:\"\";}i:28;a:5:{s:2:\"id\";i:28;s:4:\"name\";s:3:\"USA\";s:10:\"iso_code_2\";s:2:\"US\";s:10:\"iso_code_3\";s:3:\"USA\";s:4:\"lang\";s:0:\"\";}i:29;a:5:{s:2:\"id\";i:29;s:4:\"name\";s:10:\"Australien\";s:10:\"iso_code_2\";s:2:\"AU\";s:10:\"iso_code_3\";s:3:\"AUS\";s:4:\"lang\";s:0:\"\";}i:30;a:5:{s:2:\"id\";i:30;s:4:\"name\";s:6:\"Kanada\";s:10:\"iso_code_2\";s:2:\"CA\";s:10:\"iso_code_3\";s:3:\"CAN\";s:4:\"lang\";s:0:\"\";}i:31;a:5:{s:2:\"id\";i:31;s:4:\"name\";s:5:\"China\";s:10:\"iso_code_2\";s:2:\"CN\";s:10:\"iso_code_3\";s:3:\"CHN\";s:4:\"lang\";s:0:\"\";}i:32;a:5:{s:2:\"id\";i:32;s:4:\"name\";s:5:\"Japan\";s:10:\"iso_code_2\";s:2:\"JP\";s:10:\"iso_code_3\";s:3:\"JPN\";s:4:\"lang\";s:0:\"\";}i:33;a:5:{s:2:\"id\";i:33;s:4:\"name\";s:7:\"Litauen\";s:10:\"iso_code_2\";s:2:\"LT\";s:10:\"iso_code_3\";s:3:\"LTU\";s:4:\"lang\";s:0:\"\";}i:34;a:5:{s:2:\"id\";i:34;s:4:\"name\";s:13:\"Liechtenstein\";s:10:\"iso_code_2\";s:2:\"LI\";s:10:\"iso_code_3\";s:3:\"LIE\";s:4:\"lang\";s:0:\"\";}i:35;a:5:{s:2:\"id\";i:35;s:4:\"name\";s:6:\"Monaco\";s:10:\"iso_code_2\";s:2:\"MC\";s:10:\"iso_code_3\";s:3:\"MCO\";s:4:\"lang\";s:0:\"\";}i:36;a:5:{s:2:\"id\";i:36;s:4:\"name\";s:6:\"Mexico\";s:10:\"iso_code_2\";s:2:\"MX\";s:10:\"iso_code_3\";s:3:\"MEX\";s:4:\"lang\";s:0:\"\";}i:37;a:5:{s:2:\"id\";i:37;s:4:\"name\";s:17:\"Kanarische Inseln\";s:10:\"iso_code_2\";s:0:\"\";s:10:\"iso_code_3\";s:0:\"\";s:4:\"lang\";s:0:\"\";}i:38;a:5:{s:2:\"id\";i:38;s:4:\"name\";s:6:\"Indien\";s:10:\"iso_code_2\";s:2:\"IN\";s:10:\"iso_code_3\";s:3:\"IND\";s:4:\"lang\";s:0:\"\";}i:39;a:5:{s:2:\"id\";i:39;s:4:\"name\";s:9:\"Brasilien\";s:10:\"iso_code_2\";s:2:\"BR\";s:10:\"iso_code_3\";s:3:\"BRA\";s:4:\"lang\";s:0:\"\";}i:40;a:5:{s:2:\"id\";i:40;s:4:\"name\";s:8:\"Russland\";s:10:\"iso_code_2\";s:2:\"RU\";s:10:\"iso_code_3\";s:3:\"RUS\";s:4:\"lang\";s:0:\"\";}i:41;a:5:{s:2:\"id\";i:41;s:4:\"name\";s:9:\"Rumänien\";s:10:\"iso_code_2\";s:2:\"RO\";s:10:\"iso_code_3\";s:3:\"ROU\";s:4:\"lang\";s:0:\"\";}i:42;a:5:{s:2:\"id\";i:42;s:4:\"name\";s:29:\"Vereinigte Arabische Emiraten\";s:10:\"iso_code_2\";s:2:\"AE\";s:10:\"iso_code_3\";s:3:\"ARE\";s:4:\"lang\";s:0:\"\";}i:44;a:5:{s:2:\"id\";i:44;s:4:\"name\";s:9:\"Bulgarien\";s:10:\"iso_code_2\";s:2:\"BG\";s:10:\"iso_code_3\";s:3:\"BGR\";s:4:\"lang\";s:0:\"\";}i:45;a:5:{s:2:\"id\";i:45;s:4:\"name\";s:6:\"Kosovo\";s:10:\"iso_code_2\";s:2:\"XZ\";s:10:\"iso_code_3\";s:2:\"XZ\";s:4:\"lang\";s:0:\"\";}i:46;a:5:{s:2:\"id\";i:46;s:4:\"name\";s:11:\"Kirgisistan\";s:10:\"iso_code_2\";s:2:\"KG\";s:10:\"iso_code_3\";s:2:\"KG\";s:4:\"lang\";s:0:\"\";}i:47;a:5:{s:2:\"id\";i:47;s:4:\"name\";s:10:\"Kasachstan\";s:10:\"iso_code_2\";s:2:\"KZ\";s:10:\"iso_code_3\";s:3:\"KAZ\";s:4:\"lang\";s:0:\"\";}i:48;a:5:{s:2:\"id\";i:48;s:4:\"name\";s:13:\"Weißrussland\";s:10:\"iso_code_2\";s:2:\"BY\";s:10:\"iso_code_3\";s:3:\"BLR\";s:4:\"lang\";s:0:\"\";}i:49;a:5:{s:2:\"id\";i:49;s:4:\"name\";s:10:\"Usbekistan\";s:10:\"iso_code_2\";s:2:\"UZ\";s:10:\"iso_code_3\";s:3:\"UZB\";s:4:\"lang\";s:0:\"\";}i:50;a:5:{s:2:\"id\";i:50;s:4:\"name\";s:7:\"Marokko\";s:10:\"iso_code_2\";s:2:\"MA\";s:10:\"iso_code_3\";s:3:\"MAR\";s:4:\"lang\";s:0:\"\";}i:51;a:5:{s:2:\"id\";i:51;s:4:\"name\";s:8:\"Armenien\";s:10:\"iso_code_2\";s:2:\"AM\";s:10:\"iso_code_3\";s:3:\"ARM\";s:4:\"lang\";s:0:\"\";}i:52;a:5:{s:2:\"id\";i:52;s:4:\"name\";s:8:\"Albanien\";s:10:\"iso_code_2\";s:2:\"AL\";s:10:\"iso_code_3\";s:3:\"ALB\";s:4:\"lang\";s:0:\"\";}i:53;a:5:{s:2:\"id\";i:53;s:4:\"name\";s:8:\"Ägypten\";s:10:\"iso_code_2\";s:2:\"EG\";s:10:\"iso_code_3\";s:3:\"EGY\";s:4:\"lang\";s:0:\"\";}i:54;a:5:{s:2:\"id\";i:54;s:4:\"name\";s:8:\"Kroatien\";s:10:\"iso_code_2\";s:2:\"HR\";s:10:\"iso_code_3\";s:3:\"CRO\";s:4:\"lang\";s:0:\"\";}i:55;a:5:{s:2:\"id\";i:55;s:4:\"name\";s:9:\"Malediven\";s:10:\"iso_code_2\";s:2:\"MV\";s:10:\"iso_code_3\";s:3:\"MDV\";s:4:\"lang\";s:0:\"\";}i:56;a:5:{s:2:\"id\";i:56;s:4:\"name\";s:8:\"Malaysia\";s:10:\"iso_code_2\";s:2:\"MY\";s:10:\"iso_code_3\";s:3:\"MAS\";s:4:\"lang\";s:0:\"\";}i:57;a:5:{s:2:\"id\";i:57;s:4:\"name\";s:8:\"Hongkong\";s:10:\"iso_code_2\";s:2:\"HK\";s:10:\"iso_code_3\";s:3:\"HKG\";s:4:\"lang\";s:0:\"\";}i:58;a:5:{s:2:\"id\";i:58;s:4:\"name\";s:5:\"Jemen\";s:10:\"iso_code_2\";s:2:\"YE\";s:10:\"iso_code_3\";s:3:\"YEM\";s:4:\"lang\";s:0:\"\";}i:59;a:5:{s:2:\"id\";i:59;s:4:\"name\";s:6:\"Israel\";s:10:\"iso_code_2\";s:2:\"IL\";s:10:\"iso_code_3\";s:3:\"ISR\";s:4:\"lang\";s:0:\"\";}i:60;a:5:{s:2:\"id\";i:60;s:4:\"name\";s:6:\"Taiwan\";s:10:\"iso_code_2\";s:2:\"TW\";s:10:\"iso_code_3\";s:3:\"TWN\";s:4:\"lang\";s:0:\"\";}i:61;a:5:{s:2:\"id\";i:61;s:4:\"name\";s:10:\"Guadeloupe\";s:10:\"iso_code_2\";s:2:\"GP\";s:10:\"iso_code_3\";s:3:\"GLP\";s:4:\"lang\";s:0:\"\";}i:62;a:5:{s:2:\"id\";i:62;s:4:\"name\";s:8:\"Thailand\";s:10:\"iso_code_2\";s:2:\"TH\";s:10:\"iso_code_3\";s:3:\"THA\";s:4:\"lang\";s:0:\"\";}i:63;a:5:{s:2:\"id\";i:63;s:4:\"name\";s:7:\"Türkei\";s:10:\"iso_code_2\";s:2:\"TR\";s:10:\"iso_code_3\";s:3:\"TUR\";s:4:\"lang\";s:0:\"\";}i:64;a:5:{s:2:\"id\";i:64;s:4:\"name\";s:20:\"Griechenland, Inseln\";s:10:\"iso_code_2\";s:2:\"GR\";s:10:\"iso_code_3\";s:3:\"GRC\";s:4:\"lang\";s:0:\"\";}i:65;a:5:{s:2:\"id\";i:65;s:4:\"name\";s:17:\"Spanien, Balearen\";s:10:\"iso_code_2\";s:2:\"ES\";s:10:\"iso_code_3\";s:3:\"ESP\";s:4:\"lang\";s:0:\"\";}i:66;a:5:{s:2:\"id\";i:66;s:4:\"name\";s:10:\"Neuseeland\";s:10:\"iso_code_2\";s:2:\"NZ\";s:10:\"iso_code_3\";s:3:\"NZL\";s:4:\"lang\";s:0:\"\";}i:67;a:5:{s:2:\"id\";i:67;s:4:\"name\";s:11:\"Afghanistan\";s:10:\"iso_code_2\";s:2:\"AF\";s:10:\"iso_code_3\";s:3:\"AFG\";s:4:\"lang\";s:0:\"\";}i:68;a:5:{s:2:\"id\";i:68;s:4:\"name\";s:5:\"Aland\";s:10:\"iso_code_2\";s:2:\"AX\";s:10:\"iso_code_3\";s:3:\"ALA\";s:4:\"lang\";s:0:\"\";}i:69;a:5:{s:2:\"id\";i:69;s:4:\"name\";s:8:\"Algerien\";s:10:\"iso_code_2\";s:2:\"DZ\";s:10:\"iso_code_3\";s:3:\"DZA\";s:4:\"lang\";s:0:\"\";}i:70;a:5:{s:2:\"id\";i:70;s:4:\"name\";s:18:\"Amerikanisch-Samoa\";s:10:\"iso_code_2\";s:2:\"AS\";s:10:\"iso_code_3\";s:3:\"ASM\";s:4:\"lang\";s:0:\"\";}i:71;a:5:{s:2:\"id\";i:71;s:4:\"name\";s:7:\"Andorra\";s:10:\"iso_code_2\";s:2:\"AD\";s:10:\"iso_code_3\";s:3:\"AND\";s:4:\"lang\";s:0:\"\";}i:72;a:5:{s:2:\"id\";i:72;s:4:\"name\";s:6:\"Angola\";s:10:\"iso_code_2\";s:2:\"AO\";s:10:\"iso_code_3\";s:3:\"AGO\";s:4:\"lang\";s:0:\"\";}i:73;a:5:{s:2:\"id\";i:73;s:4:\"name\";s:8:\"Anguilla\";s:10:\"iso_code_2\";s:2:\"AI\";s:10:\"iso_code_3\";s:3:\"AIA\";s:4:\"lang\";s:0:\"\";}i:74;a:5:{s:2:\"id\";i:74;s:4:\"name\";s:9:\"Antarktis\";s:10:\"iso_code_2\";s:2:\"AQ\";s:10:\"iso_code_3\";s:3:\"ATA\";s:4:\"lang\";s:0:\"\";}i:75;a:5:{s:2:\"id\";i:75;s:4:\"name\";s:19:\"Antigua und Barbuda\";s:10:\"iso_code_2\";s:2:\"AG\";s:10:\"iso_code_3\";s:3:\"ATG\";s:4:\"lang\";s:0:\"\";}i:76;a:5:{s:2:\"id\";i:76;s:4:\"name\";s:11:\"Argentinien\";s:10:\"iso_code_2\";s:2:\"AR\";s:10:\"iso_code_3\";s:3:\"ARG\";s:4:\"lang\";s:0:\"\";}i:77;a:5:{s:2:\"id\";i:77;s:4:\"name\";s:5:\"Aruba\";s:10:\"iso_code_2\";s:2:\"AW\";s:10:\"iso_code_3\";s:3:\"ABW\";s:4:\"lang\";s:0:\"\";}i:78;a:5:{s:2:\"id\";i:78;s:4:\"name\";s:13:\"Aserbaidschan\";s:10:\"iso_code_2\";s:2:\"AZ\";s:10:\"iso_code_3\";s:3:\"AZE\";s:4:\"lang\";s:0:\"\";}i:79;a:5:{s:2:\"id\";i:79;s:4:\"name\";s:7:\"Bahamas\";s:10:\"iso_code_2\";s:2:\"BS\";s:10:\"iso_code_3\";s:3:\"BHS\";s:4:\"lang\";s:0:\"\";}i:80;a:5:{s:2:\"id\";i:80;s:4:\"name\";s:7:\"Bahrain\";s:10:\"iso_code_2\";s:2:\"BH\";s:10:\"iso_code_3\";s:3:\"BHR\";s:4:\"lang\";s:0:\"\";}i:81;a:5:{s:2:\"id\";i:81;s:4:\"name\";s:10:\"Bangladesh\";s:10:\"iso_code_2\";s:2:\"BD\";s:10:\"iso_code_3\";s:3:\"BGD\";s:4:\"lang\";s:0:\"\";}i:82;a:5:{s:2:\"id\";i:82;s:4:\"name\";s:8:\"Barbados\";s:10:\"iso_code_2\";s:2:\"BB\";s:10:\"iso_code_3\";s:3:\"BRB\";s:4:\"lang\";s:0:\"\";}i:83;a:5:{s:2:\"id\";i:83;s:4:\"name\";s:6:\"Belize\";s:10:\"iso_code_2\";s:2:\"BZ\";s:10:\"iso_code_3\";s:3:\"BLZ\";s:4:\"lang\";s:0:\"\";}i:84;a:5:{s:2:\"id\";i:84;s:4:\"name\";s:5:\"Benin\";s:10:\"iso_code_2\";s:2:\"BJ\";s:10:\"iso_code_3\";s:3:\"BEN\";s:4:\"lang\";s:0:\"\";}i:85;a:5:{s:2:\"id\";i:85;s:4:\"name\";s:7:\"Bermuda\";s:10:\"iso_code_2\";s:2:\"BM\";s:10:\"iso_code_3\";s:3:\"BMU\";s:4:\"lang\";s:0:\"\";}i:86;a:5:{s:2:\"id\";i:86;s:4:\"name\";s:6:\"Bhutan\";s:10:\"iso_code_2\";s:2:\"BT\";s:10:\"iso_code_3\";s:3:\"BTN\";s:4:\"lang\";s:0:\"\";}i:87;a:5:{s:2:\"id\";i:87;s:4:\"name\";s:8:\"Bolivien\";s:10:\"iso_code_2\";s:2:\"BO\";s:10:\"iso_code_3\";s:3:\"BOL\";s:4:\"lang\";s:0:\"\";}i:88;a:5:{s:2:\"id\";i:88;s:4:\"name\";s:23:\"Bosnien und Herzegowina\";s:10:\"iso_code_2\";s:2:\"BA\";s:10:\"iso_code_3\";s:3:\"BIH\";s:4:\"lang\";s:0:\"\";}i:89;a:5:{s:2:\"id\";i:89;s:4:\"name\";s:8:\"Botswana\";s:10:\"iso_code_2\";s:2:\"BW\";s:10:\"iso_code_3\";s:3:\"BWA\";s:4:\"lang\";s:0:\"\";}i:90;a:5:{s:2:\"id\";i:90;s:4:\"name\";s:12:\"Bouvetinseln\";s:10:\"iso_code_2\";s:2:\"BV\";s:10:\"iso_code_3\";s:3:\"BVT\";s:4:\"lang\";s:0:\"\";}i:91;a:5:{s:2:\"id\";i:91;s:4:\"name\";s:41:\"Britisches Territorium im Indischen Ozean\";s:10:\"iso_code_2\";s:2:\"IO\";s:10:\"iso_code_3\";s:3:\"IOT\";s:4:\"lang\";s:0:\"\";}i:92;a:5:{s:2:\"id\";i:92;s:4:\"name\";s:17:\"Brunei Darussalam\";s:10:\"iso_code_2\";s:2:\"BN\";s:10:\"iso_code_3\";s:3:\"BRN\";s:4:\"lang\";s:0:\"\";}i:93;a:5:{s:2:\"id\";i:93;s:4:\"name\";s:12:\"Burkina Faso\";s:10:\"iso_code_2\";s:2:\"BF\";s:10:\"iso_code_3\";s:3:\"BFA\";s:4:\"lang\";s:0:\"\";}i:94;a:5:{s:2:\"id\";i:94;s:4:\"name\";s:7:\"Burundi\";s:10:\"iso_code_2\";s:2:\"BI\";s:10:\"iso_code_3\";s:3:\"BDI\";s:4:\"lang\";s:0:\"\";}i:95;a:5:{s:2:\"id\";i:95;s:4:\"name\";s:10:\"Kambodscha\";s:10:\"iso_code_2\";s:2:\"KH\";s:10:\"iso_code_3\";s:3:\"KHM\";s:4:\"lang\";s:0:\"\";}i:96;a:5:{s:2:\"id\";i:96;s:4:\"name\";s:7:\"Kamerun\";s:10:\"iso_code_2\";s:2:\"CM\";s:10:\"iso_code_3\";s:3:\"CMR\";s:4:\"lang\";s:0:\"\";}i:97;a:5:{s:2:\"id\";i:97;s:4:\"name\";s:9:\"Kap Verde\";s:10:\"iso_code_2\";s:2:\"CV\";s:10:\"iso_code_3\";s:3:\"CPV\";s:4:\"lang\";s:0:\"\";}i:98;a:5:{s:2:\"id\";i:98;s:4:\"name\";s:12:\"Kaimaninseln\";s:10:\"iso_code_2\";s:2:\"KY\";s:10:\"iso_code_3\";s:3:\"CYM\";s:4:\"lang\";s:0:\"\";}i:99;a:5:{s:2:\"id\";i:99;s:4:\"name\";s:28:\"Zentralafrikanische Republik\";s:10:\"iso_code_2\";s:2:\"CF\";s:10:\"iso_code_3\";s:3:\"CAF\";s:4:\"lang\";s:0:\"\";}i:100;a:5:{s:2:\"id\";i:100;s:4:\"name\";s:6:\"Tschad\";s:10:\"iso_code_2\";s:2:\"TD\";s:10:\"iso_code_3\";s:3:\"TCD\";s:4:\"lang\";s:0:\"\";}i:101;a:5:{s:2:\"id\";i:101;s:4:\"name\";s:5:\"Chile\";s:10:\"iso_code_2\";s:2:\"CL\";s:10:\"iso_code_3\";s:3:\"CHL\";s:4:\"lang\";s:0:\"\";}i:102;a:5:{s:2:\"id\";i:102;s:4:\"name\";s:15:\"Weihnachtsinsel\";s:10:\"iso_code_2\";s:2:\"CX\";s:10:\"iso_code_3\";s:3:\"CXR\";s:4:\"lang\";s:0:\"\";}i:103;a:5:{s:2:\"id\";i:103;s:4:\"name\";s:28:\"Kokosinseln (Keelinginseln) \";s:10:\"iso_code_2\";s:2:\"CC\";s:10:\"iso_code_3\";s:3:\"CCK\";s:4:\"lang\";s:0:\"\";}i:104;a:5:{s:2:\"id\";i:104;s:4:\"name\";s:9:\"Kolumbien\";s:10:\"iso_code_2\";s:2:\"CO\";s:10:\"iso_code_3\";s:3:\"COL\";s:4:\"lang\";s:0:\"\";}i:105;a:5:{s:2:\"id\";i:105;s:4:\"name\";s:7:\"Komoren\";s:10:\"iso_code_2\";s:2:\"KM\";s:10:\"iso_code_3\";s:3:\"COM\";s:4:\"lang\";s:0:\"\";}i:106;a:5:{s:2:\"id\";i:106;s:4:\"name\";s:5:\"Kongo\";s:10:\"iso_code_2\";s:2:\"CG\";s:10:\"iso_code_3\";s:3:\"COG\";s:4:\"lang\";s:0:\"\";}i:107;a:5:{s:2:\"id\";i:107;s:4:\"name\";s:30:\"Kongo, Demokratische Republik \";s:10:\"iso_code_2\";s:2:\"CD\";s:10:\"iso_code_3\";s:3:\"COD\";s:4:\"lang\";s:0:\"\";}i:108;a:5:{s:2:\"id\";i:108;s:4:\"name\";s:10:\"Cookinseln\";s:10:\"iso_code_2\";s:2:\"CK\";s:10:\"iso_code_3\";s:3:\"COK\";s:4:\"lang\";s:0:\"\";}i:109;a:5:{s:2:\"id\";i:109;s:4:\"name\";s:10:\"Costa Rica\";s:10:\"iso_code_2\";s:2:\"CR\";s:10:\"iso_code_3\";s:3:\"CRI\";s:4:\"lang\";s:0:\"\";}i:110;a:5:{s:2:\"id\";i:110;s:4:\"name\";s:32:\"Elfenbeinküste (Côte d\'Ivoire)\";s:10:\"iso_code_2\";s:2:\"CI\";s:10:\"iso_code_3\";s:3:\"CIV\";s:4:\"lang\";s:0:\"\";}i:112;a:5:{s:2:\"id\";i:112;s:4:\"name\";s:4:\"Kuba\";s:10:\"iso_code_2\";s:2:\"CU\";s:10:\"iso_code_3\";s:3:\"CUB\";s:4:\"lang\";s:0:\"\";}i:113;a:5:{s:2:\"id\";i:113;s:4:\"name\";s:10:\"Dschibouti\";s:10:\"iso_code_2\";s:2:\"DJ\";s:10:\"iso_code_3\";s:3:\"DJI\";s:4:\"lang\";s:0:\"\";}i:114;a:5:{s:2:\"id\";i:114;s:4:\"name\";s:8:\"Dominica\";s:10:\"iso_code_2\";s:2:\"DM\";s:10:\"iso_code_3\";s:3:\"DMA\";s:4:\"lang\";s:0:\"\";}i:115;a:5:{s:2:\"id\";i:115;s:4:\"name\";s:18:\"Dominican Republic\";s:10:\"iso_code_2\";s:2:\"DO\";s:10:\"iso_code_3\";s:3:\"DOM\";s:4:\"lang\";s:0:\"\";}i:116;a:5:{s:2:\"id\";i:116;s:4:\"name\";s:7:\"Ecuador\";s:10:\"iso_code_2\";s:2:\"EC\";s:10:\"iso_code_3\";s:3:\"ECU\";s:4:\"lang\";s:0:\"\";}i:117;a:5:{s:2:\"id\";i:117;s:4:\"name\";s:11:\"El Salvador\";s:10:\"iso_code_2\";s:2:\"SV\";s:10:\"iso_code_3\";s:3:\"SLV\";s:4:\"lang\";s:0:\"\";}i:118;a:5:{s:2:\"id\";i:118;s:4:\"name\";s:17:\"Equatorial Guinea\";s:10:\"iso_code_2\";s:2:\"GQ\";s:10:\"iso_code_3\";s:3:\"GNQ\";s:4:\"lang\";s:0:\"\";}i:119;a:5:{s:2:\"id\";i:119;s:4:\"name\";s:7:\"Eritrea\";s:10:\"iso_code_2\";s:2:\"ER\";s:10:\"iso_code_3\";s:3:\"ERI\";s:4:\"lang\";s:0:\"\";}i:120;a:5:{s:2:\"id\";i:120;s:4:\"name\";s:10:\"Äthiopien\";s:10:\"iso_code_2\";s:2:\"ET\";s:10:\"iso_code_3\";s:3:\"ETH\";s:4:\"lang\";s:0:\"\";}i:121;a:5:{s:2:\"id\";i:121;s:4:\"name\";s:27:\"Falkland Islands (malvinas)\";s:10:\"iso_code_2\";s:2:\"FK\";s:10:\"iso_code_3\";s:3:\"FLK\";s:4:\"lang\";s:0:\"\";}i:122;a:5:{s:2:\"id\";i:122;s:4:\"name\";s:9:\"Färöer \";s:10:\"iso_code_2\";s:2:\"FO\";s:10:\"iso_code_3\";s:3:\"FRO\";s:4:\"lang\";s:0:\"\";}i:123;a:5:{s:2:\"id\";i:123;s:4:\"name\";s:7:\"Fidschi\";s:10:\"iso_code_2\";s:2:\"FJ\";s:10:\"iso_code_3\";s:3:\"FJI\";s:4:\"lang\";s:0:\"\";}i:124;a:5:{s:2:\"id\";i:124;s:4:\"name\";s:20:\"Französisch Guayana\";s:10:\"iso_code_2\";s:2:\"GF\";s:10:\"iso_code_3\";s:3:\"GUF\";s:4:\"lang\";s:0:\"\";}i:125;a:5:{s:2:\"id\";i:125;s:4:\"name\";s:23:\"Französisch Polynesien\";s:10:\"iso_code_2\";s:2:\"PF\";s:10:\"iso_code_3\";s:3:\"PYF\";s:4:\"lang\";s:0:\"\";}i:126;a:5:{s:2:\"id\";i:126;s:4:\"name\";s:40:\"Französische Süd- und Antarktisgebiete\";s:10:\"iso_code_2\";s:2:\"TF\";s:10:\"iso_code_3\";s:3:\"ATF\";s:4:\"lang\";s:0:\"\";}i:127;a:5:{s:2:\"id\";i:127;s:4:\"name\";s:5:\"Gabon\";s:10:\"iso_code_2\";s:2:\"GA\";s:10:\"iso_code_3\";s:3:\"GAB\";s:4:\"lang\";s:0:\"\";}i:128;a:5:{s:2:\"id\";i:128;s:4:\"name\";s:6:\"Gambia\";s:10:\"iso_code_2\";s:2:\"GM\";s:10:\"iso_code_3\";s:3:\"GMB\";s:4:\"lang\";s:0:\"\";}i:129;a:5:{s:2:\"id\";i:129;s:4:\"name\";s:8:\"Georgien\";s:10:\"iso_code_2\";s:2:\"GE\";s:10:\"iso_code_3\";s:3:\"GEO\";s:4:\"lang\";s:0:\"\";}i:130;a:5:{s:2:\"id\";i:130;s:4:\"name\";s:5:\"Ghana\";s:10:\"iso_code_2\";s:2:\"GH\";s:10:\"iso_code_3\";s:3:\"GHA\";s:4:\"lang\";s:0:\"\";}i:131;a:5:{s:2:\"id\";i:131;s:4:\"name\";s:9:\"Gibraltar\";s:10:\"iso_code_2\";s:2:\"GI\";s:10:\"iso_code_3\";s:3:\"GIB\";s:4:\"lang\";s:0:\"\";}i:132;a:5:{s:2:\"id\";i:132;s:4:\"name\";s:9:\"Grönland\";s:10:\"iso_code_2\";s:2:\"GL\";s:10:\"iso_code_3\";s:3:\"GRL\";s:4:\"lang\";s:0:\"\";}i:133;a:5:{s:2:\"id\";i:133;s:4:\"name\";s:7:\"Grenada\";s:10:\"iso_code_2\";s:2:\"GD\";s:10:\"iso_code_3\";s:3:\"GRD\";s:4:\"lang\";s:0:\"\";}i:134;a:5:{s:2:\"id\";i:134;s:4:\"name\";s:4:\"Guam\";s:10:\"iso_code_2\";s:2:\"GU\";s:10:\"iso_code_3\";s:3:\"GUM\";s:4:\"lang\";s:0:\"\";}i:135;a:5:{s:2:\"id\";i:135;s:4:\"name\";s:9:\"Guatemala\";s:10:\"iso_code_2\";s:2:\"GT\";s:10:\"iso_code_3\";s:3:\"GTM\";s:4:\"lang\";s:0:\"\";}i:136;a:5:{s:2:\"id\";i:136;s:4:\"name\";s:8:\"Guernsey\";s:10:\"iso_code_2\";s:2:\"GG\";s:10:\"iso_code_3\";s:3:\"GGY\";s:4:\"lang\";s:0:\"\";}i:137;a:5:{s:2:\"id\";i:137;s:4:\"name\";s:6:\"Guinea\";s:10:\"iso_code_2\";s:2:\"GN\";s:10:\"iso_code_3\";s:3:\"GIN\";s:4:\"lang\";s:0:\"\";}i:138;a:5:{s:2:\"id\";i:138;s:4:\"name\";s:13:\"Guinea-Bissau\";s:10:\"iso_code_2\";s:2:\"GW\";s:10:\"iso_code_3\";s:3:\"GNB\";s:4:\"lang\";s:0:\"\";}i:139;a:5:{s:2:\"id\";i:139;s:4:\"name\";s:6:\"Guyana\";s:10:\"iso_code_2\";s:2:\"GY\";s:10:\"iso_code_3\";s:3:\"GUY\";s:4:\"lang\";s:0:\"\";}i:140;a:5:{s:2:\"id\";i:140;s:4:\"name\";s:5:\"Haiti\";s:10:\"iso_code_2\";s:2:\"HT\";s:10:\"iso_code_3\";s:3:\"HTI\";s:4:\"lang\";s:0:\"\";}i:141;a:5:{s:2:\"id\";i:141;s:4:\"name\";s:24:\"Heard und McDonaldinseln\";s:10:\"iso_code_2\";s:2:\"HM\";s:10:\"iso_code_3\";s:3:\"HMD\";s:4:\"lang\";s:0:\"\";}i:142;a:5:{s:2:\"id\";i:142;s:4:\"name\";s:24:\"Heiliger Stuhl (Vatican)\";s:10:\"iso_code_2\";s:2:\"VA\";s:10:\"iso_code_3\";s:3:\"VAT\";s:4:\"lang\";s:0:\"\";}i:143;a:5:{s:2:\"id\";i:143;s:4:\"name\";s:8:\"Honduras\";s:10:\"iso_code_2\";s:2:\"HN\";s:10:\"iso_code_3\";s:3:\"HND\";s:4:\"lang\";s:0:\"\";}i:144;a:5:{s:2:\"id\";i:144;s:4:\"name\";s:6:\"Island\";s:10:\"iso_code_2\";s:2:\"IS\";s:10:\"iso_code_3\";s:3:\"ISL\";s:4:\"lang\";s:0:\"\";}i:145;a:5:{s:2:\"id\";i:145;s:4:\"name\";s:10:\"Indonesien\";s:10:\"iso_code_2\";s:2:\"ID\";s:10:\"iso_code_3\";s:3:\"IDN\";s:4:\"lang\";s:0:\"\";}i:146;a:5:{s:2:\"id\";i:146;s:4:\"name\";s:4:\"Iran\";s:10:\"iso_code_2\";s:2:\"IR\";s:10:\"iso_code_3\";s:3:\"IRN\";s:4:\"lang\";s:0:\"\";}i:147;a:5:{s:2:\"id\";i:147;s:4:\"name\";s:4:\"Irak\";s:10:\"iso_code_2\";s:2:\"IQ\";s:10:\"iso_code_3\";s:3:\"IRQ\";s:4:\"lang\";s:0:\"\";}i:148;a:5:{s:2:\"id\";i:148;s:4:\"name\";s:23:\"Insel Man (Isle of Man)\";s:10:\"iso_code_2\";s:2:\"IM\";s:10:\"iso_code_3\";s:3:\"IMM\";s:4:\"lang\";s:0:\"\";}i:149;a:5:{s:2:\"id\";i:149;s:4:\"name\";s:7:\"Jamaika\";s:10:\"iso_code_2\";s:2:\"JM\";s:10:\"iso_code_3\";s:3:\"JAM\";s:4:\"lang\";s:0:\"\";}i:150;a:5:{s:2:\"id\";i:150;s:4:\"name\";s:6:\"Jersey\";s:10:\"iso_code_2\";s:2:\"JE\";s:10:\"iso_code_3\";s:3:\"JEY\";s:4:\"lang\";s:0:\"\";}i:151;a:5:{s:2:\"id\";i:151;s:4:\"name\";s:9:\"Jordanien\";s:10:\"iso_code_2\";s:2:\"JO\";s:10:\"iso_code_3\";s:3:\"JOR\";s:4:\"lang\";s:0:\"\";}i:152;a:5:{s:2:\"id\";i:152;s:4:\"name\";s:5:\"Kenia\";s:10:\"iso_code_2\";s:2:\"KE\";s:10:\"iso_code_3\";s:3:\"KEN\";s:4:\"lang\";s:0:\"\";}i:153;a:5:{s:2:\"id\";i:153;s:4:\"name\";s:8:\"Kiribati\";s:10:\"iso_code_2\";s:2:\"KI\";s:10:\"iso_code_3\";s:3:\"KIR\";s:4:\"lang\";s:0:\"\";}i:154;a:5:{s:2:\"id\";i:154;s:4:\"name\";s:33:\"Demokratische Volksrepublik Korea\";s:10:\"iso_code_2\";s:2:\"KP\";s:10:\"iso_code_3\";s:3:\"PRK\";s:4:\"lang\";s:0:\"\";}i:155;a:5:{s:2:\"id\";i:155;s:4:\"name\";s:14:\"Republik Korea\";s:10:\"iso_code_2\";s:2:\"KR\";s:10:\"iso_code_3\";s:3:\"KOR\";s:4:\"lang\";s:0:\"\";}i:156;a:5:{s:2:\"id\";i:156;s:4:\"name\";s:6:\"Kuwait\";s:10:\"iso_code_2\";s:2:\"KW\";s:10:\"iso_code_3\";s:3:\"KWT\";s:4:\"lang\";s:0:\"\";}i:158;a:5:{s:2:\"id\";i:158;s:4:\"name\";s:4:\"Laos\";s:10:\"iso_code_2\";s:2:\"LA\";s:10:\"iso_code_3\";s:3:\"LAO\";s:4:\"lang\";s:0:\"\";}i:159;a:5:{s:2:\"id\";i:159;s:4:\"name\";s:7:\"Libanon\";s:10:\"iso_code_2\";s:2:\"LB\";s:10:\"iso_code_3\";s:3:\"LBN\";s:4:\"lang\";s:0:\"\";}i:160;a:5:{s:2:\"id\";i:160;s:4:\"name\";s:7:\"Lesotho\";s:10:\"iso_code_2\";s:2:\"LS\";s:10:\"iso_code_3\";s:3:\"LSO\";s:4:\"lang\";s:0:\"\";}i:161;a:5:{s:2:\"id\";i:161;s:4:\"name\";s:7:\"Liberia\";s:10:\"iso_code_2\";s:2:\"LR\";s:10:\"iso_code_3\";s:3:\"LBR\";s:4:\"lang\";s:0:\"\";}i:162;a:5:{s:2:\"id\";i:162;s:4:\"name\";s:7:\"Libyen \";s:10:\"iso_code_2\";s:2:\"LY\";s:10:\"iso_code_3\";s:3:\"LBY\";s:4:\"lang\";s:0:\"\";}i:163;a:5:{s:2:\"id\";i:163;s:4:\"name\";s:5:\"Macau\";s:10:\"iso_code_2\";s:2:\"MO\";s:10:\"iso_code_3\";s:3:\"MAC\";s:4:\"lang\";s:0:\"\";}i:164;a:5:{s:2:\"id\";i:164;s:4:\"name\";s:10:\"Mazedonien\";s:10:\"iso_code_2\";s:2:\"MK\";s:10:\"iso_code_3\";s:3:\"MKD\";s:4:\"lang\";s:0:\"\";}i:165;a:5:{s:2:\"id\";i:165;s:4:\"name\";s:10:\"Madagaskar\";s:10:\"iso_code_2\";s:2:\"MG\";s:10:\"iso_code_3\";s:3:\"MDG\";s:4:\"lang\";s:0:\"\";}i:166;a:5:{s:2:\"id\";i:166;s:4:\"name\";s:6:\"Malawi\";s:10:\"iso_code_2\";s:2:\"MW\";s:10:\"iso_code_3\";s:3:\"MWI\";s:4:\"lang\";s:0:\"\";}i:168;a:5:{s:2:\"id\";i:168;s:4:\"name\";s:4:\"Mali\";s:10:\"iso_code_2\";s:2:\"ML\";s:10:\"iso_code_3\";s:3:\"MLI\";s:4:\"lang\";s:0:\"\";}i:169;a:5:{s:2:\"id\";i:169;s:4:\"name\";s:14:\"Marshallinseln\";s:10:\"iso_code_2\";s:2:\"MH\";s:10:\"iso_code_3\";s:3:\"MHL\";s:4:\"lang\";s:0:\"\";}i:170;a:5:{s:2:\"id\";i:170;s:4:\"name\";s:10:\"Martinique\";s:10:\"iso_code_2\";s:2:\"MQ\";s:10:\"iso_code_3\";s:3:\"MTQ\";s:4:\"lang\";s:0:\"\";}i:171;a:5:{s:2:\"id\";i:171;s:4:\"name\";s:11:\"Mauretanien\";s:10:\"iso_code_2\";s:2:\"MR\";s:10:\"iso_code_3\";s:3:\"MRT\";s:4:\"lang\";s:0:\"\";}i:172;a:5:{s:2:\"id\";i:172;s:4:\"name\";s:9:\"Mauritius\";s:10:\"iso_code_2\";s:2:\"MU\";s:10:\"iso_code_3\";s:3:\"MUS\";s:4:\"lang\";s:0:\"\";}i:173;a:5:{s:2:\"id\";i:173;s:4:\"name\";s:7:\"Mayotte\";s:10:\"iso_code_2\";s:2:\"YT\";s:10:\"iso_code_3\";s:3:\"MYT\";s:4:\"lang\";s:0:\"\";}i:174;a:5:{s:2:\"id\";i:174;s:4:\"name\";s:11:\"Mikronesien\";s:10:\"iso_code_2\";s:2:\"FM\";s:10:\"iso_code_3\";s:3:\"FSM\";s:4:\"lang\";s:0:\"\";}i:175;a:5:{s:2:\"id\";i:175;s:4:\"name\";s:18:\"Moldavien (Moldau)\";s:10:\"iso_code_2\";s:2:\"MD\";s:10:\"iso_code_3\";s:3:\"MDA\";s:4:\"lang\";s:0:\"\";}i:176;a:5:{s:2:\"id\";i:176;s:4:\"name\";s:8:\"Mongolei\";s:10:\"iso_code_2\";s:2:\"MN\";s:10:\"iso_code_3\";s:3:\"MNG\";s:4:\"lang\";s:0:\"\";}i:177;a:5:{s:2:\"id\";i:177;s:4:\"name\";s:10:\"Montenegro\";s:10:\"iso_code_2\";s:2:\"ME\";s:10:\"iso_code_3\";s:3:\"MNE\";s:4:\"lang\";s:0:\"\";}i:178;a:5:{s:2:\"id\";i:178;s:4:\"name\";s:10:\"Montserrat\";s:10:\"iso_code_2\";s:2:\"MS\";s:10:\"iso_code_3\";s:3:\"MSR\";s:4:\"lang\";s:0:\"\";}i:179;a:5:{s:2:\"id\";i:179;s:4:\"name\";s:8:\"Mosambik\";s:10:\"iso_code_2\";s:2:\"MZ\";s:10:\"iso_code_3\";s:3:\"MOZ\";s:4:\"lang\";s:0:\"\";}i:180;a:5:{s:2:\"id\";i:180;s:4:\"name\";s:8:\"Myanmar \";s:10:\"iso_code_2\";s:2:\"MM\";s:10:\"iso_code_3\";s:3:\"MMR\";s:4:\"lang\";s:0:\"\";}i:181;a:5:{s:2:\"id\";i:181;s:4:\"name\";s:7:\"Namibia\";s:10:\"iso_code_2\";s:2:\"NA\";s:10:\"iso_code_3\";s:3:\"NAM\";s:4:\"lang\";s:0:\"\";}i:182;a:5:{s:2:\"id\";i:182;s:4:\"name\";s:5:\"Nauru\";s:10:\"iso_code_2\";s:2:\"NR\";s:10:\"iso_code_3\";s:3:\"NRU\";s:4:\"lang\";s:0:\"\";}i:183;a:5:{s:2:\"id\";i:183;s:4:\"name\";s:5:\"Nepal\";s:10:\"iso_code_2\";s:2:\"NP\";s:10:\"iso_code_3\";s:3:\"NPL\";s:4:\"lang\";s:0:\"\";}i:184;a:5:{s:2:\"id\";i:184;s:4:\"name\";s:25:\"Niederländische Antillen\";s:10:\"iso_code_2\";s:2:\"AN\";s:10:\"iso_code_3\";s:3:\"ANT\";s:4:\"lang\";s:0:\"\";}i:185;a:5:{s:2:\"id\";i:185;s:4:\"name\";s:13:\"Neukaledonien\";s:10:\"iso_code_2\";s:2:\"NC\";s:10:\"iso_code_3\";s:3:\"NCL\";s:4:\"lang\";s:0:\"\";}i:186;a:5:{s:2:\"id\";i:186;s:4:\"name\";s:9:\"Nicaragua\";s:10:\"iso_code_2\";s:2:\"NI\";s:10:\"iso_code_3\";s:3:\"NIC\";s:4:\"lang\";s:0:\"\";}i:187;a:5:{s:2:\"id\";i:187;s:4:\"name\";s:5:\"Niger\";s:10:\"iso_code_2\";s:2:\"NE\";s:10:\"iso_code_3\";s:3:\"NER\";s:4:\"lang\";s:0:\"\";}i:188;a:5:{s:2:\"id\";i:188;s:4:\"name\";s:7:\"Nigeria\";s:10:\"iso_code_2\";s:2:\"NG\";s:10:\"iso_code_3\";s:3:\"NGA\";s:4:\"lang\";s:0:\"\";}i:189;a:5:{s:2:\"id\";i:189;s:4:\"name\";s:4:\"Niue\";s:10:\"iso_code_2\";s:2:\"NU\";s:10:\"iso_code_3\";s:3:\"NIU\";s:4:\"lang\";s:0:\"\";}i:190;a:5:{s:2:\"id\";i:190;s:4:\"name\";s:12:\"Norfolkinsel\";s:10:\"iso_code_2\";s:2:\"NF\";s:10:\"iso_code_3\";s:3:\"NFK\";s:4:\"lang\";s:0:\"\";}i:191;a:5:{s:2:\"id\";i:191;s:4:\"name\";s:19:\"Nördliche Marianen\";s:10:\"iso_code_2\";s:2:\"MP\";s:10:\"iso_code_3\";s:3:\"MNP\";s:4:\"lang\";s:0:\"\";}i:192;a:5:{s:2:\"id\";i:192;s:4:\"name\";s:4:\"Oman\";s:10:\"iso_code_2\";s:2:\"OM\";s:10:\"iso_code_3\";s:3:\"OMN\";s:4:\"lang\";s:0:\"\";}i:193;a:5:{s:2:\"id\";i:193;s:4:\"name\";s:8:\"Pakistan\";s:10:\"iso_code_2\";s:2:\"PK\";s:10:\"iso_code_3\";s:3:\"PAK\";s:4:\"lang\";s:0:\"\";}i:194;a:5:{s:2:\"id\";i:194;s:4:\"name\";s:5:\"Palau\";s:10:\"iso_code_2\";s:2:\"PW\";s:10:\"iso_code_3\";s:3:\"PLW\";s:4:\"lang\";s:0:\"\";}i:195;a:5:{s:2:\"id\";i:195;s:4:\"name\";s:30:\"Die Palästinensischen Gebiete\";s:10:\"iso_code_2\";s:2:\"PS\";s:10:\"iso_code_3\";s:3:\"PSE\";s:4:\"lang\";s:0:\"\";}i:196;a:5:{s:2:\"id\";i:196;s:4:\"name\";s:6:\"Panama\";s:10:\"iso_code_2\";s:2:\"PA\";s:10:\"iso_code_3\";s:3:\"PAN\";s:4:\"lang\";s:0:\"\";}i:197;a:5:{s:2:\"id\";i:197;s:4:\"name\";s:15:\"Papua-Neuguinea\";s:10:\"iso_code_2\";s:2:\"PG\";s:10:\"iso_code_3\";s:3:\"PNG\";s:4:\"lang\";s:0:\"\";}i:198;a:5:{s:2:\"id\";i:198;s:4:\"name\";s:8:\"Paraguay\";s:10:\"iso_code_2\";s:2:\"PY\";s:10:\"iso_code_3\";s:3:\"PRY\";s:4:\"lang\";s:0:\"\";}i:199;a:5:{s:2:\"id\";i:199;s:4:\"name\";s:4:\"Peru\";s:10:\"iso_code_2\";s:2:\"PE\";s:10:\"iso_code_3\";s:3:\"PER\";s:4:\"lang\";s:0:\"\";}i:200;a:5:{s:2:\"id\";i:200;s:4:\"name\";s:11:\"Philippinen\";s:10:\"iso_code_2\";s:2:\"PH\";s:10:\"iso_code_3\";s:3:\"PHL\";s:4:\"lang\";s:0:\"\";}i:201;a:5:{s:2:\"id\";i:201;s:4:\"name\";s:14:\"Pitcairninseln\";s:10:\"iso_code_2\";s:2:\"PN\";s:10:\"iso_code_3\";s:3:\"PCN\";s:4:\"lang\";s:0:\"\";}i:202;a:5:{s:2:\"id\";i:202;s:4:\"name\";s:11:\"Puerto Rico\";s:10:\"iso_code_2\";s:2:\"PR\";s:10:\"iso_code_3\";s:3:\"PRI\";s:4:\"lang\";s:0:\"\";}i:203;a:5:{s:2:\"id\";i:203;s:4:\"name\";s:5:\"Katar\";s:10:\"iso_code_2\";s:2:\"QA\";s:10:\"iso_code_3\";s:3:\"QAT\";s:4:\"lang\";s:0:\"\";}i:204;a:5:{s:2:\"id\";i:204;s:4:\"name\";s:8:\"Réunion\";s:10:\"iso_code_2\";s:2:\"RE\";s:10:\"iso_code_3\";s:3:\"REU\";s:4:\"lang\";s:0:\"\";}i:205;a:5:{s:2:\"id\";i:205;s:4:\"name\";s:6:\"Ruanda\";s:10:\"iso_code_2\";s:2:\"RW\";s:10:\"iso_code_3\";s:3:\"RWA\";s:4:\"lang\";s:0:\"\";}i:206;a:5:{s:2:\"id\";i:206;s:4:\"name\";s:10:\"St. Helena\";s:10:\"iso_code_2\";s:2:\"SH\";s:10:\"iso_code_3\";s:3:\"SHN\";s:4:\"lang\";s:0:\"\";}i:207;a:5:{s:2:\"id\";i:207;s:4:\"name\";s:19:\"St. Kitts und Nevis\";s:10:\"iso_code_2\";s:2:\"KN\";s:10:\"iso_code_3\";s:3:\"KNA\";s:4:\"lang\";s:0:\"\";}i:208;a:5:{s:2:\"id\";i:208;s:4:\"name\";s:9:\"St. Lucia\";s:10:\"iso_code_2\";s:2:\"LC\";s:10:\"iso_code_3\";s:3:\"LCA\";s:4:\"lang\";s:0:\"\";}i:209;a:5:{s:2:\"id\";i:209;s:4:\"name\";s:23:\"St. Pierre und Miquelon\";s:10:\"iso_code_2\";s:2:\"PM\";s:10:\"iso_code_3\";s:3:\"SPM\";s:4:\"lang\";s:0:\"\";}i:210;a:5:{s:2:\"id\";i:210;s:4:\"name\";s:30:\"St. Vincent und die Grenadinen\";s:10:\"iso_code_2\";s:2:\"VC\";s:10:\"iso_code_3\";s:3:\"VCT\";s:4:\"lang\";s:0:\"\";}i:211;a:5:{s:2:\"id\";i:211;s:4:\"name\";s:5:\"Samoa\";s:10:\"iso_code_2\";s:2:\"WS\";s:10:\"iso_code_3\";s:3:\"WSM\";s:4:\"lang\";s:0:\"\";}i:212;a:5:{s:2:\"id\";i:212;s:4:\"name\";s:10:\"San Marino\";s:10:\"iso_code_2\";s:2:\"SM\";s:10:\"iso_code_3\";s:3:\"SMR\";s:4:\"lang\";s:0:\"\";}i:213;a:5:{s:2:\"id\";i:213;s:4:\"name\";s:23:\"Sao Tomé und Príncipe\";s:10:\"iso_code_2\";s:2:\"ST\";s:10:\"iso_code_3\";s:3:\"STP\";s:4:\"lang\";s:0:\"\";}i:214;a:5:{s:2:\"id\";i:214;s:4:\"name\";s:13:\"Saudi Arabien\";s:10:\"iso_code_2\";s:2:\"SA\";s:10:\"iso_code_3\";s:3:\"SAU\";s:4:\"lang\";s:0:\"\";}i:215;a:5:{s:2:\"id\";i:215;s:4:\"name\";s:7:\"Senegal\";s:10:\"iso_code_2\";s:2:\"SN\";s:10:\"iso_code_3\";s:3:\"SEN\";s:4:\"lang\";s:0:\"\";}i:216;a:5:{s:2:\"id\";i:216;s:4:\"name\";s:7:\"Serbien\";s:10:\"iso_code_2\";s:2:\"RS\";s:10:\"iso_code_3\";s:3:\"SRB\";s:4:\"lang\";s:0:\"\";}i:217;a:5:{s:2:\"id\";i:217;s:4:\"name\";s:10:\"Seychellen\";s:10:\"iso_code_2\";s:2:\"SC\";s:10:\"iso_code_3\";s:3:\"SYC\";s:4:\"lang\";s:0:\"\";}i:218;a:5:{s:2:\"id\";i:218;s:4:\"name\";s:12:\"Sierra Leone\";s:10:\"iso_code_2\";s:2:\"SL\";s:10:\"iso_code_3\";s:3:\"SLE\";s:4:\"lang\";s:0:\"\";}i:219;a:5:{s:2:\"id\";i:219;s:4:\"name\";s:9:\"Solomonen\";s:10:\"iso_code_2\";s:2:\"SB\";s:10:\"iso_code_3\";s:3:\"SLB\";s:4:\"lang\";s:0:\"\";}i:220;a:5:{s:2:\"id\";i:220;s:4:\"name\";s:7:\"Somalia\";s:10:\"iso_code_2\";s:2:\"SO\";s:10:\"iso_code_3\";s:3:\"SOM\";s:4:\"lang\";s:0:\"\";}i:221;a:5:{s:2:\"id\";i:221;s:4:\"name\";s:10:\"Südafrika\";s:10:\"iso_code_2\";s:2:\"ZA\";s:10:\"iso_code_3\";s:3:\"ZAF\";s:4:\"lang\";s:0:\"\";}i:222;a:5:{s:2:\"id\";i:222;s:4:\"name\";s:46:\"Südgeorgien und die Südlichen Sandwichinseln\";s:10:\"iso_code_2\";s:2:\"GS\";s:10:\"iso_code_3\";s:3:\"SGS\";s:4:\"lang\";s:0:\"\";}i:223;a:5:{s:2:\"id\";i:223;s:4:\"name\";s:9:\"Sri Lanka\";s:10:\"iso_code_2\";s:2:\"LK\";s:10:\"iso_code_3\";s:3:\"LKA\";s:4:\"lang\";s:0:\"\";}i:224;a:5:{s:2:\"id\";i:224;s:4:\"name\";s:5:\"Sudan\";s:10:\"iso_code_2\";s:2:\"SD\";s:10:\"iso_code_3\";s:3:\"SDN\";s:4:\"lang\";s:0:\"\";}i:225;a:5:{s:2:\"id\";i:225;s:4:\"name\";s:8:\"Suriname\";s:10:\"iso_code_2\";s:2:\"SR\";s:10:\"iso_code_3\";s:3:\"SUR\";s:4:\"lang\";s:0:\"\";}i:226;a:5:{s:2:\"id\";i:226;s:4:\"name\";s:21:\"Spitzb. und Jan Mayen\";s:10:\"iso_code_2\";s:2:\"SJ\";s:10:\"iso_code_3\";s:3:\"SJM\";s:4:\"lang\";s:0:\"\";}i:227;a:5:{s:2:\"id\";i:227;s:4:\"name\";s:9:\"Swaziland\";s:10:\"iso_code_2\";s:2:\"SZ\";s:10:\"iso_code_3\";s:3:\"SWZ\";s:4:\"lang\";s:0:\"\";}i:228;a:5:{s:2:\"id\";i:228;s:4:\"name\";s:6:\"Syrien\";s:10:\"iso_code_2\";s:2:\"SY\";s:10:\"iso_code_3\";s:3:\"SYR\";s:4:\"lang\";s:0:\"\";}i:229;a:5:{s:2:\"id\";i:229;s:4:\"name\";s:13:\"Tadschikistan\";s:10:\"iso_code_2\";s:2:\"TJ\";s:10:\"iso_code_3\";s:3:\"TJK\";s:4:\"lang\";s:0:\"\";}i:230;a:5:{s:2:\"id\";i:230;s:4:\"name\";s:8:\"Tansania\";s:10:\"iso_code_2\";s:2:\"TZ\";s:10:\"iso_code_3\";s:3:\"TZA\";s:4:\"lang\";s:0:\"\";}i:231;a:5:{s:2:\"id\";i:231;s:4:\"name\";s:11:\"Timor-Leste\";s:10:\"iso_code_2\";s:2:\"TL\";s:10:\"iso_code_3\";s:3:\"TLS\";s:4:\"lang\";s:0:\"\";}i:232;a:5:{s:2:\"id\";i:232;s:4:\"name\";s:4:\"Togo\";s:10:\"iso_code_2\";s:2:\"TG\";s:10:\"iso_code_3\";s:3:\"TGO\";s:4:\"lang\";s:0:\"\";}i:233;a:5:{s:2:\"id\";i:233;s:4:\"name\";s:7:\"Tokelau\";s:10:\"iso_code_2\";s:2:\"TK\";s:10:\"iso_code_3\";s:3:\"TKL\";s:4:\"lang\";s:0:\"\";}i:234;a:5:{s:2:\"id\";i:234;s:4:\"name\";s:5:\"Tonga\";s:10:\"iso_code_2\";s:2:\"TO\";s:10:\"iso_code_3\";s:3:\"TON\";s:4:\"lang\";s:0:\"\";}i:235;a:5:{s:2:\"id\";i:235;s:4:\"name\";s:19:\"Trinidad und Tobago\";s:10:\"iso_code_2\";s:2:\"TT\";s:10:\"iso_code_3\";s:3:\"TTO\";s:4:\"lang\";s:0:\"\";}i:236;a:5:{s:2:\"id\";i:236;s:4:\"name\";s:8:\"Tunesien\";s:10:\"iso_code_2\";s:2:\"TN\";s:10:\"iso_code_3\";s:3:\"TUN\";s:4:\"lang\";s:0:\"\";}i:237;a:5:{s:2:\"id\";i:237;s:4:\"name\";s:12:\"Turkmenistan\";s:10:\"iso_code_2\";s:2:\"TM\";s:10:\"iso_code_3\";s:3:\"TKM\";s:4:\"lang\";s:0:\"\";}i:238;a:5:{s:2:\"id\";i:238;s:4:\"name\";s:23:\"Turks- und Caicosinseln\";s:10:\"iso_code_2\";s:2:\"TC\";s:10:\"iso_code_3\";s:3:\"TCA\";s:4:\"lang\";s:0:\"\";}i:239;a:5:{s:2:\"id\";i:239;s:4:\"name\";s:6:\"Tuvalu\";s:10:\"iso_code_2\";s:2:\"TV\";s:10:\"iso_code_3\";s:3:\"TUV\";s:4:\"lang\";s:0:\"\";}i:240;a:5:{s:2:\"id\";i:240;s:4:\"name\";s:6:\"Uganda\";s:10:\"iso_code_2\";s:2:\"UG\";s:10:\"iso_code_3\";s:3:\"UGA\";s:4:\"lang\";s:0:\"\";}i:241;a:5:{s:2:\"id\";i:241;s:4:\"name\";s:7:\"Ukraine\";s:10:\"iso_code_2\";s:2:\"UA\";s:10:\"iso_code_3\";s:3:\"UKR\";s:4:\"lang\";s:0:\"\";}i:242;a:5:{s:2:\"id\";i:242;s:4:\"name\";s:36:\"United States Minor Outlying Islands\";s:10:\"iso_code_2\";s:2:\"UM\";s:10:\"iso_code_3\";s:3:\"UMI\";s:4:\"lang\";s:0:\"\";}i:243;a:5:{s:2:\"id\";i:243;s:4:\"name\";s:7:\"Uruguay\";s:10:\"iso_code_2\";s:2:\"UY\";s:10:\"iso_code_3\";s:3:\"URY\";s:4:\"lang\";s:0:\"\";}i:244;a:5:{s:2:\"id\";i:244;s:4:\"name\";s:7:\"Vanuatu\";s:10:\"iso_code_2\";s:2:\"VU\";s:10:\"iso_code_3\";s:3:\"VUT\";s:4:\"lang\";s:0:\"\";}i:245;a:5:{s:2:\"id\";i:245;s:4:\"name\";s:9:\"Venezuela\";s:10:\"iso_code_2\";s:2:\"VE\";s:10:\"iso_code_3\";s:3:\"VEN\";s:4:\"lang\";s:0:\"\";}i:246;a:5:{s:2:\"id\";i:246;s:4:\"name\";s:7:\"Vietnam\";s:10:\"iso_code_2\";s:2:\"VN\";s:10:\"iso_code_3\";s:3:\"VNM\";s:4:\"lang\";s:0:\"\";}i:247;a:5:{s:2:\"id\";i:247;s:4:\"name\";s:23:\"Virgin-Inseln (British)\";s:10:\"iso_code_2\";s:2:\"VG\";s:10:\"iso_code_3\";s:3:\"VGB\";s:4:\"lang\";s:0:\"\";}i:248;a:5:{s:2:\"id\";i:248;s:4:\"name\";s:20:\"Virgin-Inseln (U.S.)\";s:10:\"iso_code_2\";s:2:\"VI\";s:10:\"iso_code_3\";s:3:\"VIR\";s:4:\"lang\";s:0:\"\";}i:249;a:5:{s:2:\"id\";i:249;s:4:\"name\";s:17:\"Wallis und Futuna\";s:10:\"iso_code_2\";s:2:\"WF\";s:10:\"iso_code_3\";s:3:\"WLF\";s:4:\"lang\";s:0:\"\";}i:250;a:5:{s:2:\"id\";i:250;s:4:\"name\";s:10:\"Westsahara\";s:10:\"iso_code_2\";s:2:\"EH\";s:10:\"iso_code_3\";s:3:\"ESH\";s:4:\"lang\";s:0:\"\";}i:252;a:5:{s:2:\"id\";i:252;s:4:\"name\";s:6:\"Sambia\";s:10:\"iso_code_2\";s:2:\"ZM\";s:10:\"iso_code_3\";s:3:\"ZMB\";s:4:\"lang\";s:0:\"\";}i:253;a:5:{s:2:\"id\";i:253;s:4:\"name\";s:8:\"Simbabwe\";s:10:\"iso_code_2\";s:2:\"ZW\";s:10:\"iso_code_3\";s:3:\"ZWE\";s:4:\"lang\";s:0:\"\";}i:255;a:5:{s:2:\"id\";i:255;s:4:\"name\";s:22:\"Helgoland, Deutschland\";s:10:\"iso_code_2\";s:2:\"DE\";s:10:\"iso_code_3\";s:3:\"DEU\";s:4:\"lang\";s:4:\"NULL\";}}'),
				('MiscCurrenciesSerialized','a:16:{i:0;s:3:\"EUR\";i:1;s:3:\"USD\";i:2;s:3:\"CHF\";i:3;s:3:\"GBP\";i:4;s:3:\"PLN\";i:5;s:3:\"AUD\";i:6;s:3:\"INR\";i:7;s:3:\"NOK\";i:8;s:3:\"DKK\";i:9;s:3:\"SEK\";i:10;s:3:\"CZK\";i:11;s:3:\"HUF\";i:12;s:3:\"TRY\";i:13;s:3:\"RUB\";i:14;s:3:\"BRL\";i:15;s:3:\"CAD\";}')
		");

		Shopware()->Db()->exec("
			CREATE TABLE `plenty_log` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `timestamp` int(11) unsigned NOT NULL,
			  `identifier` varchar(100) NOT NULL DEFAULT '',
			  `type` tinyint(4) unsigned NOT NULL,
			  `message` text NOT NULL,
			  `code` int(10) unsigned DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  KEY `type` (`type`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		Shopware()->Db()->exec("
			CREATE TABLE `plenty_mapping_attribute_group` (
			  `shopwareID` int(11) unsigned NOT NULL,
			  `plentyID` int(11) unsigned NOT NULL,
			  PRIMARY KEY (`shopwareID`,`plentyID`),
			  UNIQUE KEY `plentyID` (`plentyID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		Shopware()->Db()->exec("
			CREATE TABLE `plenty_mapping_attribute_option` (
			  `shopwareID` int(11) unsigned NOT NULL,
			  `plentyID` int(11) unsigned NOT NULL,
			  PRIMARY KEY (`shopwareID`,`plentyID`),
			  UNIQUE KEY `plentyID` (`plentyID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		Shopware()->Db()->exec("
			CREATE TABLE `plenty_mapping_category` (
			  `shopwareID` varchar(255) NOT NULL DEFAULT '',
			  `plentyID` varchar(255) NOT NULL DEFAULT '',
			  PRIMARY KEY (`shopwareID`,`plentyID`),
			  UNIQUE KEY `plentyID` (`plentyID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		Shopware()->Db()->exec("
			CREATE TABLE `plenty_mapping_country` (
			  `shopwareID` int(11) unsigned NOT NULL,
			  `plentyID` int(11) unsigned NOT NULL,
			  PRIMARY KEY (`shopwareID`,`plentyID`),
			  UNIQUE KEY `plentyID` (`plentyID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		Shopware()->Db()->exec("
			CREATE TABLE `plenty_mapping_currency` (
			  `shopwareID` varchar(5) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			  `plentyID` varchar(5) NOT NULL DEFAULT '',
			  PRIMARY KEY (`shopwareID`,`plentyID`),
			  UNIQUE KEY `plentyID` (`plentyID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		Shopware()->Db()->exec("
			CREATE TABLE `plenty_mapping_customer` (
			  `shopwareID` int(11) unsigned NOT NULL,
			  `plentyID` int(11) unsigned NOT NULL,
			  PRIMARY KEY (`shopwareID`,`plentyID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		Shopware()->Db()->exec("
			CREATE TABLE `plenty_mapping_customer_billing_address` (
			  `shopwareID` int(11) unsigned NOT NULL,
			  `plentyID` int(11) unsigned NOT NULL,
			  PRIMARY KEY (`shopwareID`,`plentyID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		Shopware()->Db()->exec("
			CREATE TABLE `plenty_mapping_customer_class` (
			  `shopwareID` int(11) unsigned NOT NULL,
			  `plentyID` int(11) NOT NULL,
			  PRIMARY KEY (`shopwareID`,`plentyID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table may only be used to retrieve the plentymarkets customer id with a shopware order billing address id.';
		");

		Shopware()->Db()->exec("
			CREATE TABLE `plenty_mapping_item` (
			  `shopwareID` int(11) unsigned NOT NULL,
			  `plentyID` int(11) unsigned NOT NULL,
			  PRIMARY KEY (`shopwareID`,`plentyID`),
			  UNIQUE KEY `plentyID` (`plentyID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		Shopware()->Db()->exec("
			CREATE TABLE `plenty_mapping_item_bundle` (
			  `shopwareID` int(11) unsigned NOT NULL,
			  `plentyID` int(11) unsigned NOT NULL,
			  PRIMARY KEY (`shopwareID`,`plentyID`),
			  UNIQUE KEY `plentyID` (`plentyID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		Shopware()->Db()->exec("
			CREATE TABLE `plenty_mapping_item_variant` (
			  `shopwareID` int(11) unsigned NOT NULL,
			  `plentyID` varchar(255) NOT NULL DEFAULT '',
			  PRIMARY KEY (`shopwareID`,`plentyID`),
			  UNIQUE KEY `plentyID` (`plentyID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		Shopware()->Db()->exec("
			CREATE TABLE `plenty_mapping_measure_unit` (
			  `shopwareID` int(11) unsigned NOT NULL,
			  `plentyID` varchar(5) NOT NULL DEFAULT '',
			  PRIMARY KEY (`shopwareID`,`plentyID`),
			  UNIQUE KEY `plentyID` (`plentyID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		Shopware()->Db()->exec("
			CREATE TABLE `plenty_mapping_method_of_payment` (
			  `shopwareID` int(11) unsigned NOT NULL,
			  `plentyID` int(11) unsigned NOT NULL,
			  PRIMARY KEY (`shopwareID`,`plentyID`),
			  UNIQUE KEY `plentyID` (`plentyID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		Shopware()->Db()->exec("
			CREATE TABLE `plenty_mapping_producer` (
			  `shopwareID` int(11) unsigned NOT NULL,
			  `plentyID` int(11) unsigned NOT NULL,
			  PRIMARY KEY (`shopwareID`,`plentyID`),
			  UNIQUE KEY `plentyID` (`plentyID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		Shopware()->Db()->exec("
			CREATE TABLE `plenty_mapping_property` (
			  `shopwareID` varchar(255) NOT NULL DEFAULT '',
			  `plentyID` int(11) unsigned NOT NULL,
			  PRIMARY KEY (`shopwareID`,`plentyID`),
			  UNIQUE KEY `plentyID` (`plentyID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		Shopware()->Db()->exec("
			CREATE TABLE `plenty_mapping_property_group` (
			  `shopwareID` int(11) unsigned NOT NULL,
			  `plentyID` int(11) unsigned NOT NULL,
			  PRIMARY KEY (`shopwareID`,`plentyID`),
			  UNIQUE KEY `plentyID` (`plentyID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		Shopware()->Db()->exec("
			CREATE TABLE `plenty_mapping_referrer` (
			  `shopwareID` int(11) unsigned NOT NULL,
			  `plentyID` int(11) unsigned NOT NULL,
			  PRIMARY KEY (`shopwareID`,`plentyID`),
			  UNIQUE KEY `plentyID` (`plentyID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		Shopware()->Db()->exec("
			CREATE TABLE `plenty_mapping_shipping_profile` (
			  `shopwareID` int(11) unsigned NOT NULL,
			  `plentyID` varchar(255) NOT NULL DEFAULT '',
			  PRIMARY KEY (`shopwareID`,`plentyID`),
			  UNIQUE KEY `plentyID` (`plentyID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		Shopware()->Db()->exec("
			CREATE TABLE `plenty_mapping_shop` (
			  `shopwareID` int(11) unsigned NOT NULL,
			  `plentyID` int(11) unsigned NOT NULL,
			  PRIMARY KEY (`shopwareID`,`plentyID`),
			  UNIQUE KEY `plentyID` (`plentyID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		Shopware()->Db()->exec("
			CREATE TABLE `plenty_mapping_vat` (
			  `shopwareID` int(11) unsigned NOT NULL,
			  `plentyID` int(11) unsigned NOT NULL,
			  PRIMARY KEY (`shopwareID`,`plentyID`),
			  UNIQUE KEY `plentyID` (`plentyID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		Shopware()->Db()->exec("
			CREATE TABLE `plenty_order` (
			  `shopwareId` int(11) unsigned NOT NULL,
			  `plentyOrderId` int(11) unsigned DEFAULT NULL,
			  `plentyOrderTimestamp` datetime DEFAULT NULL,
			  `plentyOrderPaidTimestamp` datetime DEFAULT NULL,
			  `plentyOrderPaidStatus` int(11) DEFAULT NULL,
			  `plentyOrderStatus` decimal(4,2) DEFAULT NULL,
			  `status` int(11) NOT NULL DEFAULT '0',
			  `numberOfTries` smallint(5) unsigned NOT NULL DEFAULT '0',
			  `timestampLastTry` datetime DEFAULT NULL,
			  PRIMARY KEY (`shopwareId`),
			  UNIQUE KEY `plentyOrderId` (`plentyOrderId`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		Shopware()->Db()->exec("
			CREATE TABLE `plenty_stack_item` (
			  `itemId` int(11) unsigned NOT NULL,
			  `timestamp` int(10) unsigned NOT NULL,
			  `storeIds` text NOT NULL,
			  PRIMARY KEY (`itemId`),
			  KEY `timestamp` (`timestamp`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
	}

    /**
     * Creates and subscribe the events and hooks.
     */
    protected function createEvents()
    {
    	// Backend controller
        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_Plentymarkets',
            'onGetControllerPathBackend'
        );

        // Order-Stack-Process
	    $this->subscribeEvent(
		    'Shopware_Models_Order_Order::postPersist',
		    'onOrderSaveOrderProcessDetails'
	    );

        // Insert the CSS
        $this->subscribeEvent(
        	'Enlight_Controller_Action_PostDispatch_Backend_Index',
        	'onPostDispatchBackendIndex'
        );

    }

    /**
     * Dispatches the backend index on post event.
     *
     * @param Enlight_Event_EventArgs $arguments
     */
    public function onPostDispatchBackendIndex(Enlight_Event_EventArgs $arguments)
    {
		$request = $arguments->getSubject()->Request();

		$path = str_replace('Bootstrap.php', 'style.css', __FILE__);
		$path = preg_replace('!^(?:.*?)(/engine/Shopware.*?)$!', '$1', $path);
		$path = str_replace('/shopware.php', '', $_SERVER['PHP_SELF']) . $path;
		$path .= '?' . urlencode($this->getVersion());

    	if ($request->getActionName() == 'index')
    	{
    		$view = $arguments->getSubject()->View();
    		$view->extendsBlock(
    			'backend/base/header/css',
    			'<link href="' . $path . '" type="text/css" rel="stylesheet">',
    			'append'
			);
    	}
    }

    /**
     * Registers all cronjobs
     */
    protected function registerCronjobs()
    {

        // Export Orders
        $this->createCronJob(
        	'Plentymarkets Order Export',
        	'Shopware_CronJob_PlentymarketsOrderExportCron',
        	PlentymarketsCronjobController::INTERVAL_EXPORT_ORDER,
        	true
        );

        $this->subscribeEvent(
        	'Shopware_CronJob_PlentymarketsOrderExportCron',
        	'onRunOrderExportCron'
        );

        // Export Orders
        $this->createCronJob(
        	'Plentymarkets Order Incoming Payment Export',
        	'Shopware_CronJob_PlentymarketsOrderIncomingPaymentExportCron',
        	PlentymarketsCronjobController::INTERVAL_EXPORT_ORDER_INCOMING_PAYMENT,
        	true
        );

        $this->subscribeEvent(
        	'Shopware_CronJob_PlentymarketsOrderIncomingPaymentExportCron',
        	'onRunOrderIncomingPaymentExportCron'
        );

        // Item Import
        $this->createCronJob(
        	'Plentymarkets Item Import',
        	'Shopware_CronJob_PlentymarketsItemImportCron',
        	PlentymarketsCronjobController::INTERVAL_IMPORT_ITEM,
        	true
        );

        $this->subscribeEvent(
        	'Shopware_CronJob_PlentymarketsItemImportCron',
        	'onRunItemImportCron'
        );


        // Item Price Import
        $this->createCronJob(
        	'Plentymarkets Item Price Import',
        	'Shopware_CronJob_PlentymarketsItemPriceImportCron',
        	PlentymarketsCronjobController::INTERVAL_IMPORT_ITEM_PRICE,
        	true
        );

        $this->subscribeEvent(
        	'Shopware_CronJob_PlentymarketsItemPriceImportCron',
        	'onRunItemPriceImportCron'
        );

        // Item Stock Import
        $this->createCronJob(
        	'Plentymarkets Item Stock Import',
        	'Shopware_CronJob_PlentymarketsItemPriceStockCron',
        	PlentymarketsCronjobController::INTERVAL_IMPORT_ITEM_STOCK,
        	true
        );

        $this->subscribeEvent(
        	'Shopware_CronJob_PlentymarketsItemPriceStockCron',
        	'onRunItemStockImportCron'
        );

        // Order Import
        $this->createCronJob(
        	'Plentymarkets Order Import',
        	'Shopware_CronJob_PlentymarketsOrderImportCron',
        	PlentymarketsCronjobController::INTERVAL_IMPORT_ORDER,
        	true
        );

        $this->subscribeEvent(
        	'Shopware_CronJob_PlentymarketsOrderImportCron',
        	'onRunOrderImportCron'
        );

        // Export
        $this->createCronJob(
        	'Plentymarkets Export',
        	'Shopware_CronJob_PlentymarketsExportCron',
        	PlentymarketsCronjobController::INTERVAL_EXPORT,
        	true
        );

        $this->subscribeEvent(
        	'Shopware_CronJob_PlentymarketsExportCron',
        	'onRunExportCron'
        );

        // Cleanup (global)
        $this->createCronJob(
        	'Plentymarkets Cleanup',
        	'Shopware_CronJob_PlentymarketsCleanupCron',
        	900,
        	true
        );

        $this->subscribeEvent(
        	'Shopware_CronJob_PlentymarketsCleanupCron',
        	'onRunCleanupCron'
        );

        // Cleanup (items)
        $this->addItemCleanupCronEvent();

        // Cleanup (mapping)
        $this->addMappingCleanupCronEvent();

        // Item import stack update
        $this->addItemImportStackCronEvent();

        // Cleanup (log)
        $this->addLogCleanupCronEvent();

        // Item associate update
        $this->addItemAssociateUpdateCronEvent();

		// Item Bundle
		$this->addItemBundleCronEvents();
    }

    /**
     * Adds the cron event to sync the item bundles
     */
    private function addItemBundleCronEvents()
    {
    	try
    	{
	    	$this->createCronJob(
	    		'Plentymarkets Item Bundle Import',
	    		'Shopware_CronJob_PlentymarketsItemBundleImport',
	    		7200, // 2 hours
	    		false
	    	);

	    	$this->subscribeEvent(
	    		'Shopware_CronJob_PlentymarketsItemBundleImport',
	    		'onRunItemBundleImportCron'
	    	);
    	}
    	catch (Exception $E)
    	{
    	}

		try
    	{
	    	$this->createCronJob(
	    		'Plentymarkets Item Bundle Cleanup',
	    		'Shopware_CronJob_PlentymarketsItemBundleCleanup',
	    		14400, // 4 hours
	    		false
	    	);

	    	$this->subscribeEvent(
	    		'Shopware_CronJob_PlentymarketsItemBundleCleanup',
	    		'onRunItemBundleCleanupCron'
	    	);
    	}
    	catch (Exception $E)
    	{
    	}
    }

    /**
     * Adds the cron event to sync the item associate data
     */
    private function addItemAssociateUpdateCronEvent()
    {
    	try
    	{
	    	$this->createCronJob(
	    		'Plentymarkets Item Associate Import',
	    		'Shopware_CronJob_PlentymarketsItemAssociateImport',
	    		7200, // 2 hours
	    		true
	    	);

	    	$this->subscribeEvent(
	    		'Shopware_CronJob_PlentymarketsItemAssociateImport',
	    		'onRunItemAssociateImportCron'
	    	);
    	}
    	catch (Exception $E)
    	{
    	}
    }

    /**
     * Adds the cron event to clean up the items
     */
    private function addLogCleanupCronEvent()
    {
    	try
    	{
	    	$this->createCronJob(
	    		'Plentymarkets Log Cleanup',
	    		'Shopware_CronJob_PlentymarketsLogCleanup',
	    		604800, // 1 week
	    		true
	    	);

	    	$this->subscribeEvent(
	    		'Shopware_CronJob_PlentymarketsLogCleanup',
	    		'onRunLogCleanupCron'
	    	);
    	}
    	catch (Exception $E)
    	{
    	}
    }

    /**
     * Adds the cron event to clean up the items
     */
    private function addItemCleanupCronEvent()
    {
    	try
    	{
	    	$this->createCronJob(
	    		'Plentymarkets Item Cleanup',
	    		'Shopware_CronJob_PlentymarketsItemCleanup',
	    		43200, // 12 hours
	    		false
	    	);

	    	$this->subscribeEvent(
	    		'Shopware_CronJob_PlentymarketsItemCleanup',
	    		'onRunItemCleanupCron'
	    	);
    	}
    	catch (Exception $E)
    	{
    	}
    }

    /**
     * Adds the cron event to clean up the items
     */
    private function addMappingCleanupCronEvent()
    {
    	try
    	{
	    	$this->createCronJob(
	    		'Plentymarkets Mapping Cleanup',
	    		'Shopware_CronJob_PlentymarketsMappingCleanup',
	    		1800, // 0,5 hours
	    		true
	    	);

	    	$this->subscribeEvent(
	    		'Shopware_CronJob_PlentymarketsMappingCleanup',
	    		'onRunMappingCleanupCron'
	    	);
    	}
    	catch (Exception $E)
    	{
    	}
    }

    /**
     * Adds the cron event to update the item import stack
     */
    private function addItemImportStackCronEvent()
    {
    	try
    	{
	    	$this->createCronJob(
	    		'Plentymarkets Item Import Stack Update',
	    		'Shopware_CronJob_PlentymarketsItemImportStackUpdate',
	    		1800, // 0,5 hours
	    		true
	    	);

	    	$this->subscribeEvent(
	    		'Shopware_CronJob_PlentymarketsItemImportStackUpdate',
	    		'onRunItemImportStackUpdateCron'
	    	);
    	}
    	catch (Exception $E)
    	{
    	}
    }

    /**
     * Imports item associate data
     *
     * @param Shopware_Components_Cron_CronJob $Job
     */
	public function onRunItemAssociateImportCron(Shopware_Components_Cron_CronJob $Job)
	{
		PlentymarketsCronjobController::getInstance()->runItemAssociateImport($Job);
	}

    /**
     * Imports item bundles
     *
     * @param Shopware_Components_Cron_CronJob $Job
     */
	public function onRunItemBundleImportCron(Shopware_Components_Cron_CronJob $Job)
	{
		PlentymarketsCronjobController::getInstance()->runItemBundleImport($Job);
	}

    /**
     * Cleans the item bundles
     *
     * @param Shopware_Components_Cron_CronJob $Job
     */
	public function onRunItemBundleCleanupCron(Shopware_Components_Cron_CronJob $Job)
	{
		PlentymarketsCronjobController::getInstance()->runItemBundleCleanup($Job);
	}

    /**
     * Cleans up the log.
     *
     * @param Shopware_Components_Cron_CronJob $Job
     */
	public function onRunLogCleanupCron(Shopware_Components_Cron_CronJob $Job)
	{
		PlentymarketsCronjobController::getInstance()->runLogCleanup($Job);
	}

    /**
     * Cleans the items.
     *
     * @param Shopware_Components_Cron_CronJob $Job
     */
	public function onRunItemCleanupCron(Shopware_Components_Cron_CronJob $Job)
	{
		PlentymarketsCronjobController::getInstance()->runItemCleanup($Job);
	}

    /**
     * Cleans the mapping.
     *
     * @param Shopware_Components_Cron_CronJob $Job
     */
	public function onRunMappingCleanupCron(Shopware_Components_Cron_CronJob $Job)
	{
		PlentymarketsCronjobController::getInstance()->runMappingCleanup($Job);
	}

    /**
     * Cleans database tabels on first execution of plentymarkets plugin.
     *
     * @param Shopware_Components_Cron_CronJob $Job
     */
	public function onRunCleanupCron(Shopware_Components_Cron_CronJob $Job)
	{
		PlentymarketsCronjobController::getInstance()->runCleanup($Job);
	}

	/**
	 * Runs the order export cronjob.
	 *
	 * @param Shopware_Components_Cron_CronJob $Job
	 */
    public function onRunOrderExportCron(Shopware_Components_Cron_CronJob $Job)
    {
    	PlentymarketsCronjobController::getInstance()->runOrderExport($Job);
    }

    /**
     * Runs the order incoming payment export cronjob.
     *
     * @param Shopware_Components_Cron_CronJob $Job
     */
    public function onRunOrderIncomingPaymentExportCron(Shopware_Components_Cron_CronJob $Job)
    {
    	PlentymarketsCronjobController::getInstance()->runOrderIncomingPaymentExport($Job);
    }

    /**
     * Runs the order import cronjob.
     *
     * @param Shopware_Components_Cron_CronJob $Job
     */
    public function onRunOrderImportCron(Shopware_Components_Cron_CronJob $Job)
    {
    	PlentymarketsCronjobController::getInstance()->runOrderImport($Job);
    }

    /**
     * Runs the export cronjob.
     *
     * @param Shopware_Components_Cron_CronJob $Job
     */
    public function onRunExportCron(Shopware_Components_Cron_CronJob $Job)
    {
    	PlentymarketsCronjobController::getInstance()->runExport($Job);
    }

    /**
     * Runs the item import cronjob.
     *
     * @param Shopware_Components_Cron_CronJob $Job
     */
    public function onRunItemImportCron(Shopware_Components_Cron_CronJob $Job)
    {
    	PlentymarketsCronjobController::getInstance()->runItemImport($Job);
    }

    /**
     * Runs the item import stack update cronjob.
     *
     * @param Shopware_Components_Cron_CronJob $Job
     */
    public function onRunItemImportStackUpdateCron(Shopware_Components_Cron_CronJob $Job)
    {
    	PlentymarketsCronjobController::getInstance()->runItemImportStackUpdate($Job);
    }

    /**
     * Runs the item price import cronjob.
     *
     * @param Shopware_Components_Cron_CronJob $Job
     */
    public function onRunItemPriceImportCron(Shopware_Components_Cron_CronJob $Job)
    {
    	PlentymarketsCronjobController::getInstance()->runItemPriceImport($Job);
    }

    /**
     * Runs the item stock import cronjob.
     *
     * @param Shopware_Components_Cron_CronJob $Job
     */
    public function onRunItemStockImportCron(Shopware_Components_Cron_CronJob $Job)
    {
    	PlentymarketsCronjobController::getInstance()->runItemStockImport($Job);
    }

    /**
     * Creates the menu item
     */
    protected function createMenu()
    {
        $parent = $this->Menu()->findOneBy('label', 'Einstellungen');

        $this->createMenuItem(array(
            'label' => 'plentymarkets',
            'class' => 'plenty-p',
            'active' => 1,
            'parent' => $parent,
        	'controller' => 'Plentymarkets',
        	'action' => 'Index'
        ));
    }

    /**
     * Stores the id of the created order into the plenty_order table
     *
     * @param Enlight_Event_EventArgs $arguments
     * @return boolean
     */
    public function onOrderSaveOrderProcessDetails(Enlight_Event_EventArgs $arguments)
    {

	    $model   = $arguments->get('entity');
	    $orderId = $model->getId();

	    Shopware()->Db()->query('
          INSERT INTO plenty_order
             SET shopwareId = ?
       ', array($orderId));

	    return true;

    }

    /**
     * Returns the path to a backend controller for an event.
     *
     * @param Enlight_Event_EventArgs $args
     * @return string
     */
    public function onGetControllerPathBackend(Enlight_Event_EventArgs $args)
    {
    	$this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/', 'plentymarkets'
        );

        return PY_CONTROLLERS . 'Backend/Plentymarkets.php';
    }

    /**
     * Returns label string.
     *
     * @return string
     */
    public function getLabel()
    {
    	return 'plentymarkets';
    }

    /**
     * Returns version string.
     *
     * @return string
     */
    public function getVersion()
    {
    	return '1.6.2';
    }

    /**
     * Returns the information of plugin as array.
     *
     * @return array
     */
    public function getInfo()
    {
        return array(
			'version' => $this->getVersion(),
			'autor' => 'plentymarkets GmbH',
			'copyright' => 'Copyright © 2013-2014, plentymarkets GmbH',
			'label' => $this->getLabel(),
			'support' => 'http://www.plentymarkets.eu/service-support/',
			'link' => 'http://man.plentymarkets.eu/tools/shopware-connector/'
		);
    }

}
