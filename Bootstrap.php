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

require_once PY_COMPONENTS . 'Utils/PlentymarketsLogger.php';
require_once PY_COMPONENTS . 'Cron/CronjobController.php';

class Shopware_Plugins_Backend_Plentymarkets_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Installs the plugin
     *
     * @return bool
     */
    public function install()
    {
        $this->createDatabase();
        $this->createEvents();
        $this->createMenu();
        $this->registerCronjobs();

        return true;
    }

	/**
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
			'plenty_mapping_customer_class',
			'plenty_mapping_item',
			'plenty_mapping_item_variant',
			'plenty_mapping_measure_unit',
			'plenty_mapping_method_of_payment',
			'plenty_mapping_producer',
			'plenty_mapping_property',
			'plenty_mapping_property_group',
			'plenty_mapping_shipping_profile',
			'plenty_mapping_vat',
			'plenty_order'
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
			  `request` text NOT NULL,
			  `response` text NOT NULL,
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
			  `shopwareID` int(11) unsigned NOT NULL,
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
			  PRIMARY KEY (`shopwareID`,`plentyID`),
			  UNIQUE KEY `plentyID` (`plentyID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		Shopware()->Db()->exec("
			CREATE TABLE `plenty_mapping_customer_class` (
			  `shopwareID` int(11) unsigned NOT NULL,
			  `plentyID` int(11) NOT NULL,
			  PRIMARY KEY (`shopwareID`,`plentyID`),
			  UNIQUE KEY `plentyID` (`plentyID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
			CREATE TABLE `plenty_mapping_shipping_profile` (
			  `shopwareID` int(11) unsigned NOT NULL,
			  `plentyID` varchar(255) NOT NULL DEFAULT '',
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
	}

    /**
     * Creates and subscribe the events and hooks.
     */
    protected function createEvents()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_Plentymarkets',
            'onGetControllerPathFrontend'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_Plentymarkets',
            'onGetControllerPathBackend'
        );

        // Order-Stack-Process
        $this->subscribeEvent(
        	'Shopware_Modules_Order_SaveOrder_ProcessDetails',
        	'onOrderSaveOrderProcessDetails'
        );

        $this->subscribeEvent(
        	'Enlight_Controller_Action_PostDispatch_Backend_Index',
        	'onPostDispatchBackendIndex'
        );

    }

    public function onPostDispatchBackendIndex(Enlight_Event_EventArgs $arguments)
    {
    	$request = $arguments->getSubject()->Request();

    	if ($request->getActionName() == 'index')
    	{
    		$view = $arguments->getSubject()->View();
    		$view->extendsBlock(
    			'backend/base/header/css',
    			'<style type="text/css">
    .plenty-p { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA2ZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDowNjgwMTE3NDA3MjA2ODExOTJCMEUxQzE4ODA5MUUxMCIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDpENkNCMDJGMDUzNjQxMUUyQjJENjhCMTk3RTkyMkYxQyIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDpENkNCMDJFRjUzNjQxMUUyQjJENjhCMTk3RTkyMkYxQyIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M1IE1hY2ludG9zaCI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOjBBOUM5QjE2MEIyMDY4MTE4OEM2QzBGOUEzODA4RkU1IiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOjA2ODAxMTc0MDcyMDY4MTE5MkIwRTFDMTg4MDkxRTEwIi8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+OOKySQAAAoRJREFUeNp0k11IVEEUx/937uzedddVW2R1RTMqbQ3avh6SHqIPKFqiDxKql6geewgSjB4S8rXogyAoCaIgeuithyJISCNCKlwyrbQsNfN7Uxbdde/HdObu7nWTHPjNnTlzzpl7/odRPgUqkBtWIoGqB60oOXFcbkuJjcRmIkII4h3RRnyevH4Dv5uawbF0CHGY5jPEDqLYmJ5CvPUeCjZF4N8fPSnvIR4SZ4lkfoItUNXbvLysPmcwRkcxED2IZCwGV2kQtd3vwctDjI5OAYofChpY1nePsKzXalFRvVaz1sk4efMWUrEe8EAQIm3A/DOzeJ1lHlWg7JQJVhCPYJheV2UIrlBGE6GnkXjRBlZYCGGYUEsD4BUhJ17/OQSFsahMcIAoE7oObc1qgGeqMsbHqYQxe2/Nz8O7fRvU4hInwfyHLihu90rpvdu2mCa09WHHYaGvH/rEBBh3Q/X7EGxqXAzu7ESqu5cSaCrPtoc0UeCJbHCcXFWVKGk4AmshjeCF83QWyXbJwlhzC32EDJmRCdqpdaeZrwCe8DongVZTi+onj5e02MLIuUbMvXoD5i+Ulj6pwUthWkleFoRrVXVWYQvGxPii4Kkk5jo68CN6CPG793PBcnRyKmBEGPpTd3XVMeb1ZeSYncH3XfvAfD4wbwGMqTipPmj/dl7wNMV2ZSTXjUtaXThKK7+9HR6GPvgro45CMFUKRjUr+QU9p7MEsx2E+OapC8sHkJYnqZ5eWzzFQ0Ea4eK2yPlqENfkgjkmVX1G815iIBn7mLl5+XGViP2bQNjdbJdvYuHL1xaF86Flgu8QF3Mb/h+HWWEYlyGUK7SWD2srEZT27FN+m+/8V4ABAPM81zVPbiotAAAAAElFTkSuQmCC) no-repeat !important; }
    .plenty-export-start { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyhJREFUeNp0Um1LU2EYvs7LNs/cZjNPupK0sWnYXOuFDO0FTINaEUUqQVF/oS996OVLVB+j+hAYfsswpCIKK6hcYVlpL7Mtm2mhrljZNHRrZ2fbOafnOSOJqAsueJ77vq77vp+bh7l5hIVgZBw8Bz+ASuQxnlPQ4zmtbqSX8DG27195KaPFeDmLcsZQeHxlQ+uGsqVepyyn8aDnxpxBHt+j4tssVcuceChpqlzd5N9jM5kK8HXy7aehx91r5WzyFD+X0g7WbNrVXCRWOyfHJ/DixQAWetsEl2fjNuNhl95u+fcxjIX7cOvmDdTVrYNYWr3CuXaX8PxeZ5RPZbBGY9ny0NAARkbGUdR4Bm6PBwvMZoSCQdT6fLAvcsO9fgmSFg8e9x5FdXUlFtjN5dTLkncISnqGGRqK6OYqtxs2QdA7vx4cxG/QGM1RDdVSD/WymSyGewNvZdHXCqfTCbPRCJZhdFM6nZ4vQGM053K5IK5qBfVQL69puJiYnthcUVO/hgrnJEknRSQSwUQ8jr9R5d2MyO0TI6TNRd49zHysGsYUd3KxogAcFVSUlOjCRCIxf6bIqSq+zMyg0Fau7LjGTH2owUfeOUwSQMbAslD+MG9vakJjYyMOtLXBarXqtFgsWN3QgIa6Ot1DvXw2XzxWGI3+xLJltt/durq7sX/fPnR2dc1PoBF2hMiYwXia/KgYjXEHyHJUoMxot/u4+vqFZpNJX1gB2brf70dRcbF+puRJ7mpYxvuYrH0rKH45tqT2Lp8hVUiBnqnLl3dXtrQ4FKvVwpPnUFDz2UfTGP2eyU9ANq7KGXXrXp/pUkLdlGO4/ZySHy1plaQfs4HA8rLm5mKTKBqpmKL90Rds2VkLaVEJNEcJjBWlTDQFeLyO0tDgWAUXJqKHhAFRXBpPJt22K1emkUxyVofDINjtfGhiFoH7Ye3z0zfZz73PUqP9wZTsXSX0ddx5lctmz9EfYyJ0CoKwU1XVKZcsR5uADW5ghQFw0CnIomOjwLsHwJPJlksdUBVDLhY8L/W3X+BJnr4iLklSJxW/y/M9/gOjrAS0VHwg299+nXp/CTAA46xONM4O5awAAAAASUVORK5CYII%3D) no-repeat !important; }
    .plenty-export-restart { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAvhJREFUeNp8U2lIVGEUPe/5RjO3TJ1KC0ptdUapISrJcknFMkXrTwhG/WihBYlIWzCNqB8VTdFmq7lFC7QwOWXhH00qkLGkyUgxMbMRNfPZjM68pfvGmZKyLlwu7917zvnePe9j4IptGg+ETgRsAvDSIkPtLWNDJAOGAVjqU11MZaksY4IMdEoyaiWgh8MfMY1IdmoUADP2dTwBVqSduF8AwQHLB9Pbt/cuzbbx30o83BPrI1hE+svw93Sqjeaocjwpxq85bSjCIK9iREnlHxI2fcKUMJ/Ppvp+LjucwTpKlpExTiSQcnz6qYeFYn//r1NJDgemzl0UTb1wLmsWg39EIg0kpJ+8f8jhArMs+7ur4iBKcLD/ASelHi47xJvNEEdGINP2JElyVmbSJNzJy74gyqgbj2AVNZJTD14/MNTejskrtLBaLBBsNieYDQhA1Y50Pak/p9m/CJzglP1XCgba2qBeNg8QBKgTF+J7Vxc4Uq7clXmWZmpo1qgAxtqYIgLJGUfK93Y3NSFyeRRgtzsbrYYXmBIVhfLdWectU2NrLOplRomIBfvIL4IUYk3NKCrdw/f1wVuthrnWhAWkbH76GqFaLW4Xbr6oqD5JuptBNdA+PFxh5XknQRyB0zILr+Xxrm17eHnBLzQUTU9eYUZMDO4Ubyn5ErTYaJq/6zEBiwmjU/ZBUcGUJrKH1+af2//DavNyW6VUJf2CgrCx6nNLZ0isalgVMOD+1u25Wt3FsuZGWZL0HKn72F02OX8Sskoh8Q0MxI19uaUtSx7Fbd20PKJlEOCFUYKGXlpWllb34GZ9HifI+PS1tfljcLhGo4CV8CWrruZvqqQnQ/8waz92vHpgrFUzc1brnpVUNzIsq2fipjEzc6L983XJ2SvD5kRHqDw9cb1494O2QRgvvxMNyqEoRXf1y6mspxsiiz3mM9ZnR8u5um65zyryJR29Zd2+KkQ4RDg6h1Bz7b1oJkAwpXJwxU+HkrYO0xtIQp3QoL+l3LWfAgwAj4JL8B/5rcwAAAAASUVORK5CYII%3D) no-repeat !important; }
    .plenty-save { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAVRJREFUeNqkkr9Og1AUxk+5kMBgdCQxId19CJx8AlfnLk3s6oKxYesCK3FzNT6AL4GLm2lcmFmahlwo1/MhNKW0UONJPi45nPPj/LkjpRT9x0ZsOJ9YV7ee935K0ut8fsPHJ+sRBOjN930FKwcEQyxykKvXUE1KSQW3s9lsev8uhCDEIgePLaAsS5KcnOd5L8BgIXYfIPBnALIBgNK0pkrRAhRFQZLJQwDNMAixHQCo38slXY7HvQDEHK3gOQxP2r1lWYcriOOYjl2s+r5U5rpuB6CjAqwoCMLOJgzueza7J8dxKEmSZgZ6C4AKdF2n1Sqj6XRSOReLYFsyvsFM02xm0AIYGq8HQeu1JNu2Kyfeq9Up0QLU98CotoKeWV4URV9pmmZSqioYUupXjW8XwDlnyMVkHNYF65r1wIEvh4bIfd/tuT5YEwDOAW6m+gfDILIfAQYAAm/QA0tCHVUAAAAASUVORK5CYII%3D) no-repeat !important; }
    .plenty-OrderMarking-1 { background: url(data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAKnSURBVDjLlZJbSNNhGMaFroKCbgqCHW37W24tT6mFRZq1sougqIzwQoKoCyMxSMWyZkV4KkemCUtJcSibkznn1A3pNGYTNY+bOptb2g42TzS1kU/fAodRZl088H3wPr/vfd/vCQIQ9D8aaaJUg3Kuz1AarPDfNzYoqe0mJRVKjNtMSm6eVRUBd3MiWvLYvg0BlhbqOTHahhXUHHn1u029H/rH7BV9ER/yHFbTugBi5I6qqUVnTxqWXFosO1sx25UOV1M8BsrDoMxl5a7W/sl81tpxAO6hfHxzqTHXfR6eNwlwKnhwNMbAoTkKtYhl+g1AjDuJ2qeMyViw1mHJ/hJzxiTMvI3HtO4g3JpIuFQCuLQn0VXM8QUAoyqKS4xTZNYVd/8d+GaN+Gq5D7deCKfuMCabYzBWuxd2GR/ORtJF6wl0PAheDgCG5Vytu+8clh0SeCcy4TWlYrH/DFyv4jFaH46hSh4+lFGwSkN+jjGlPo7GbJYtAOir4kzOW65h3iLC+xo+eutDMVgXjTEipyYaxhIOup/sgr2WA3fbMUzI4lB3kykLADqfBleMqOLgMedgoOE0VPdioRMfgbaAjY8yATytYegTs2GRMOFoSUTPMx5qrjOEvyzxdTFb3yONIF1kQ3FLAK+1EF96M6HJ56OziIGZZooAWGQfJEC32Z61vxY4tD1kmw1V4TC8uIBxXQa84yKMqC6iJGUrdHd3YEHJha3hEKQ3mIN/BPhFAtKgK96HtsJYKDJ50JcloPTSFjxK2oxuMQ0WaRSqrtIn1gX4Jc9mCeVZTOhJ4uyGU/j8TgiDZA8+qXejt0yAisv0zr8CViXNYIqk6QzoCngwV0fBXBmJpqwQlKbQRP8E8Ks6jbFJcoWeU55Kd4pTaNMlybR2cTKNtbbmB+pfvh6cSOd2AAAAAElFTkSuQmCC) no-repeat !important; }
	.plenty-OrderMarking-2 { background: url(data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIwSURBVDjLlZLNS5RRFMafe9/3vjPOjI1jaKKEVH40tGgRBWEibfoPQoKkVdtoEQQF4T/QqkVtWrSTFrVsF1FgJbWpIAh1k2PNh+PrfL4f95zTQk0HHKkDD/cc7vP8uHCuEhF0q/KnmXNgGR248PZFN4/GISXMC8L89DBPV0Dp4/SsazJjrtfb9/vdxfn/BgjzY5M8Aq8nBya+V3h93vtnQHFxat4kszntJAAAxus1YvnZQV5V/jyTEZarwnwFLGeFZdT0ZFOJdD84qoCDOpQ7grZfRNj020JSEOKvwvxGiF+q0tL0N5PuO+Mk0nC0B0BDsYCCImyzAIktBBloMwKJLSgKYcMAcdhC2KpVlIig+H5qxcv0n0xmj4Gbq+BwC2wtJLbgHUlMEFJwUpMIGpto16u+kJzSACAk+WCzvNbe+AVljkOYIcQQou3TbvdOJo+g4aNdqzaF+PT43HJVA8DQpcVIiPPtaqlEUQzlDELsTpgYwgTAQIjQqlUCtpQfn1spdmxh+PJSQyw9CrbKgM7tvcISQAxlBhC3GuCYXk3cWP25m3M7dk88qbWBRDVApaATOSjPBdXXwYEP5QyCgvjE/kwHgInHtHYBnYA2owhrPiiuw0sOw3EZFEagIB7qChDiYaUcNIoFtP1KxCTPhWiDw7WbXk9vKpnOgsI4exjg6Mbq96YQPxm79uPOvqvbXx4O3KrF6w8osv2df17kr5YXJq7vnw/S0v3k7Ie7xtud/wAaRnP+Cw8iKQAAAABJRU5ErkJggg==) no-repeat !important; }
	.plenty-OrderMarking-3 { background: url(data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJ/SURBVDjLbVJBaxNBGH2bpEkTmxi1NTRKTZtoQUHEWz0Igj2I4kG9eVNQhEBO7bEHc+yv8JAiHnr2B4gFqVrQRhObljQolBSTJqZJdnZmfbNr2rU68DEz33zfm/fejGHbNrxjaWlpRCk1J6WcYZxkgPGTsWJZ1mIul/vlrTe8AIVC4Qqbl5PJ5GQsFoPP5wP36PV6qNfr2OIg0L35+fm1fwDYPMLDj+l0OmOaJmq1Gjqdjr4dgUAAiUTCqSsWixvMXV5YWOjqvW+AxOSz8fHxjBAC5XJ5s91up7gO6tDrUqn0QwOTXYZSsoO+wGDB5EwkEkGlUgGb7mSz2apHajWfz9+sVqvFVCrl1P4PYExr5m16vYUjQ+c0O11DtmN/ebD95pG9UpnGzl7Y0Xz30ir8toAtLdiWG0JIvFi76piaGG7g9plVTD/5YLgMCPLg/g0YtMTwhznfApRBfsP6kAYJSKuN57Md5oXTsvHy7aEEfZMutHZfIRAahWGMsHAICMeZVsD+HmTrG8zudyhrH+HJLGyz7wEgRSh9k4nm+nvqPIb4xWuovV5k/2lMXJ9F8+s6ARqIpk6QsIQtTC+AcGTYpBqfvgBfcJTuKMi+xKfdMCZgIp6eRK8TYu2+w2oA4PwDm+5qVK218XmNLN7xxILqKfS7pGqTWekLmuVtV65STs8hA73RqJQQP5+CP3KKACamHj7FlGBDawfH00kEW0MuA8o9AmA6qMrSHqwTIAoM08hAkHkN0ES3UYfotBGdiNFu5cr2AmgJobOPET7nhxEMuU/o40soSjO7iHbbVNgnUen6pY0/AOCTbC7PuV44H0f8Cetg5g9zP5aU7loDcfwGcrKyzYdvwUUAAAAASUVORK5CYII=) no-repeat !important; }
	.plenty-OrderMarking-4 { background: url(data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJPSURBVDjLpZPLS5RhFMYfv9QJlelTQZwRb2OKlKuINuHGLlBEBEOLxAu46oL0F0QQFdWizUCrWnjBaDHgThCMoiKkhUONTqmjmDp2GZ0UnWbmfc/ztrC+GbM2dXbv4ZzfeQ7vefKMMfifyP89IbevNNCYdkN2kawkCZKfSPZTOGTf6Y/m1uflKlC3LvsNTWArr9BT2LAf+W73dn5jHclIBFZyfYWU3or7T4K7AJmbl/yG7EtX1BQXNTVCYgtgbAEAYHlqYHlrsTEVQWr63RZFuqsfDAcdQPrGRR/JF5nKGm9xUxMyr0YBAEXXHgIANq/3ADQobD2J9fAkNiMTMSFb9z8ambMAQER3JC1XttkYGGZXoyZEGyTHRuBuPgBTUu7VSnUAgAUAWutOV2MjZGkehgYUA6O5A0AlkAyRnotiX3MLlFKduYCqAtuGXpyH0XQmOj+TIURt51OzURTYZdBKV2UBSsOIcRp/TVTT4ewK6idECAihtUKOArWcjq/B8tQ6UkUR31+OYXP4sTOdisivrkMyHodWejlXwcC38Fvs8dY5xaIId89VlJy7ACpCNCFCuOp8+BJ6A631gANQSg1mVmOxxGQYRW2nHMha4B5WA3chsv22T5/B13AIicWZmNZ6cMchTXUe81Okzz54pLi0uQWp+TmkZqMwxsBV74Or3od4OISPr0e3SHa3PX0f3HXKofNH/UIG9pZ5PeUth+CyS2EMkEqs4fPEOBJLsyske48/+xD8oxcAYPzs4QaS7RR2kbLTTOTQieczfzfTv8QPldGvTGoF6/8AAAAASUVORK5CYII=) no-repeat !important; }
	.plenty-OrderMarking-5 { background: url(data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIsSURBVDjLpVNLSJQBEP7+h6uu62vLVAJDW1KQTMrINQ1vPQzq1GOpa9EppGOHLh0kCEKL7JBEhVCHihAsESyJiE4FWShGRmauu7KYiv6Pma+DGoFrBQ7MzGFmPr5vmDFIYj1mr1WYfrHPovA9VVOqbC7e/1rS9ZlrAVDYHig5WB0oPtBI0TNrUiC5yhP9jeF4X8NPcWfopoY48XT39PjjXeF0vWkZqOjd7LJYrmGasHPCCJbHwhS9/F8M4s8baid764Xi0Ilfp5voorpJfn2wwx/r3l77TwZUvR+qajXVn8PnvocYfXYH6k2ioOaCpaIdf11ivDcayyiMVudsOYqFb60gARJYHG9DbqQFmSVNjaO3K2NpAeK90ZCqtgcrjkP9aUCXp0moetDFEeRXnYCKXhm+uTW0CkBFu4JlxzZkFlbASz4CQGQVBFeEwZm8geyiMuRVntzsL3oXV+YMkvjRsydC1U+lhwZsWXgHb+oWVAEzIwvzyVlk5igsi7DymmHlHsFQR50rjl+981Jy1Fw6Gu0ObTtnU+cgs28AKgDiy+Awpj5OACBAhZ/qh2HOo6i+NeA73jUAML4/qWux8mt6NjW1w599CS9xb0mSEqQBEDAtwqALUmBaG5FV3oYPnTHMjAwetlWksyByaukxQg2wQ9FlccaK/OXA3/uAEUDp3rNIDQ1ctSk6kHh1/jRFoaL4M4snEMeD73gQx4M4PsT1IZ5AfYH68tZY7zv/ApRMY9mnuVMvAAAAAElFTkSuQmCC) no-repeat !important; }
	.plenty-OrderMarking-6 { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAu9JREFUeNpUU0lIVVEY/s697z19z+f0wtIsMapFRRFIkwuxwFURtMply6iIsE0EBUFBVBsjEAoiahkNNoFpEaE06CaqhwM+zdm00jff6Zy+Y96iAx//uf853/dP5wqlFPzljlw3aaKBdScXfZ96K2KiQf2Ur8UR5eEJ4RF2cL/K6XPhC5C8jaaD6IeScdoHgHxjjJ2uh4cTJFUTH6WHutAB1egHEH58N9F6lWYXlNqdteSCbbuRUnOg6E3vTLax+kU4UNoo5MI03Fz/OwrdkS46I4fViIF/Ky6l2vllKOV1tQ1XpJ98LfIyeTTUxiOB2D4h7CjM/CoY2Yo90kEo0owRTQr47NsvnJKDe9yCLbWA8bQd+W+T8HqiCDZbkGsLYNprgFQOynEwnSo4XnnBai8CxpcyuNHSUp/v/Xy2+3IP0skMCgvDuEZ/pEdh4mIVrNkhZFKvYLn9GPpUhEvnCla3XV/R6GcgauPxo7uSyZXtvb14Pz6HYWVgTHczncb6kIWvbTUQOyyMDpSg6V0CTUm7hCO4wyv3dBODfRs3jtUJUdk3OIib9M4T54kNxKRpLmErQz11ORfbRgn9XZzwLaWMpR6kHScmhMB2XY5hIBcMwmGtsxzxVCCADEnPbYU0z/NEMe9+Vkr4JajpQECpRAIzWiwUgnRd2FJigWI/uU/Sr19WisQcEWZGFgP4AvJHYeHEiBDrM/z4wQPH41PTBGYwxwxyFEuTqAWC9I3SV+O6nhbQU5CjxcUdA+HwUqR5Xp5i5CnudQmLFFyk/UVyhpAkj7KkHYDlC6DfNO8+Kiv7rveejsoUxymorU/W9Yf+vH2MSWnx3T/8K/Csu/vDZDR65XJ5ua1rKmZUg1GyzCa7TCpbFrjPnjQI0XlQqTP//Qt6HaqsPJ9NpU5tsu3yaopoMS2gUxtk6oyc3gu8vCFlC13f+N/8L6AFm2OxJiOXOzbrOPVTrluhOxUyzUQV0LdZqcetUj7Tw9LT0wK/BRgA2uaDShknhJ4AAAAASUVORK5CYII=) no-repeat !important; }
	.plenty-OrderMarking-7 { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAxpJREFUeNpUU1tsTFEUXec+ZqYzfWgbjyhpKkQiRYV4VFQJ8cE3Pwgh/CASHxIfpIiQhggSCZHwJxKaIN5aj6hXSBAdlLajryk6nWln7p07d87Z9i0z4iT77pNz7lp77b3PFkSE3Mp2ntLZFRpVuxK5M3oiykQdxVSz2EQS19kkW8ZcTbZ3L3IEDJ7F7i7bJ5BqY38VUI+073tqIbGDQRVsr5TEXN8aqs8FEFC+0Y3b1djIbgGIFlqOjGfcTKhEaw8+ej1g1VfcKjBK6oWK9yNrf3rORBdVFveD66hTw7/VphTNf/8trs60vBh7O/U46BgR1E35GDTKlguRKYSeHg/NGrtIufCF1vo6PVCe4MLtTHEsYfurpzj+mxW3sLnkIY4UPYV0e6CGe4AkpzxigxwXvTH/zvdn3ao8wcHDG5Z9jT7ef6K5CQkrgeJCE+q4xJkJIawxA0gn2pEaeQhHhfHMMbCqmaoa3hau97DGgQapDYXe7PuyVpY1t4QRae2H3a1DfOeAn5MQcwhHr5VgaVJHS38AzpZ+dPlgRpR1kPGHDP4EwpVdizunToSdELhyvhf4xafHmKRC4Gk38HJCEudMQor1Wg8EqJir/0ABW1mBJ2M4K/2uyAI1AuZJA4EkkOZ7NUAIsJKUw+A+/jFDEMMMLmKSD3/a7xEoI6pThCJAlBAcBByO5Eq+jDEmTiCLQQkGjPBZCjAZZWWQJ8gWDxTEtY5UueRCp1m+4z0u/kFxNH+UybzIrMqLLvnZhDoImclilEBrOKBng9Gy+6XvvFyYbYhgcNcES1Y/CFaKRok0ViN4H3A5eg/BVyMo38Z4tOj05MuhpLeXnLvJuY9pB3wcHTwVgkmJwcLkh8sIrZtoXgHCeYJ7d962hgbLGyv368pLyi4VSAYIMs3DZDOY1dIYLlyAU2ySmF6ttdcCG/+bBW/Vrig6FXFHtsVmSr9d+bdCXED85F63EfQeyGk1IrxqNrYf26u3knD+tDG3Vi6J737SGno21JHe2/cuO+PHIPl5eDFOaIOl5eLzpBm4saAal8w0BnKY3wIMAAf0g5YxBG6YAAAAAElFTkSuQmCC) no-repeat !important; }
	.plenty-OrderMarking-8 { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAq1JREFUeNpkk89PE1EQx7/brm1tsQSSakPrAYIaiXCRiHqAhnBrbx68etMYw8XEv4CbGpPeiDF6MN48aFBjGqHxULW0JlIFUsUKyi+B0pb+7u57zmzcFXWSybx9b76fmfdjFSklTNNyUTuFNrV7vGjOyddKpzIs82JauSR1PCXXyZsHwrLG64oJIPEAhZfki5BinuJjQMRtK9fPQ8c1EgXIk0LHaUdEhswC+wE3KQxByrPVhiw0W8Ldbpv3xGc3qqHA84Nqe0gRhXVotcU3BHogNMTcF2XOhj82L4Q8M7dU0++90HypJY+nqRzFcM+CW+0cVZRmG+z1I7BVfedECw4Ws8gCkOhQvthwnupxubbWv+DZ9EdMv81BVNYhSqtAmba8V4NstLCWd17du6/4LcDExMTgt+9bN+4+2USposHd1oFEIoHM5nHcfncF9cJnVPZeoSEWkSwexoXJE3Lo5smgBWg2m5Gurq7A+7mvuPVoG9mlVRSLRaytrcHvXMHDD6OY/dGNO+kBbBdUHOsO9lWr1Qhr1d8A41R9Ph9isRhoEWNjY/B6vZjbsKFc9mLlp8TChhuJXAecTqelMQD1er1fCAG/349wOAwet1otVCoVw2kdn/IeNBoNaJpmAGi93wJwUqFQoEplQ6zrugFgQa1WMwDsVNUA8BxHC0ATGQKMsIATeZEhLOBvnucxu81mQ6lU4pyMBaDKcYfDMaIoitENV2cIRxaZkaEulws7Ozs8jlu3QCc+lc1m+fkaW+AW+SD/bd9utxtt53I5enRiygLMzMykSBBNJpMlblFVVQNkVmfjygxIp9Mlqh4laOqvf4Gtt7f3MgnGg8Fgn8fjMfZr3sbu7i6Wl5e5cpTAk//9TKYFAoFBOrQInUGIKhnXS54hj1PuFIlT+/N/CTAAxvnjsuf6W+AAAAAASUVORK5CYII=) no-repeat !important; }
	.plenty-OrderMarking-9 { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAmZJREFUeNqcU79rFEEU/mZ27ldOLxfvFGI8DCQSLWwCMYiFVtpH0kngekGsLA12aRTBPmAr5h+wsrAxYCNKAokkMefFO7kfe5u7vd3ZHd/bXZITrRz4mGHe+773Y96I508xuhThFuE+4QZhJrnfJXwkvCV8IOhRAh6vGrxYFZN0fCKlWrk4NTcxeek6iudifqe1W64ffl78Udt+EIb6NV2tEadOHIgkAya/yuUyS3NX51GZvo1srgiVPhMZtefAHXTwfe89trc+YTAYbtD1Q0LduncHFh2e5bLp6vzCXVQu30RuTCFluZDoEmxY0iMxhcL4NAqFPH419q5pHXD276KahbSqM7NXUCpNISUbVJchIiBEXKcxdOZdisiHfb983aqaMNhggeVy6UKhfL5CkVpE1BAmYQan3RUkIknYkgrsWy61Cs1mfZkFForFCSg5JIIbORomkoYZeZ4oCxOLKsqEOSSwwAKz6TQ79CKmCWOiwd+LbWwwxgJzmBs9o/ZtBNonhAhVEukfKySBQDOoUH9wMgc7jtMrea5GkBEkYCCsuIQ/w5MApR/4Ap5r4DiRwA4LbNp2f7HfDzGWs5BScXgpRkSYTPB9YOgJ9PsBbNtlyyYLvOk5/kq7YwrZTIq6HDdMURZCntauKbpLfXYcoN3x0XO0zVyVzPb6USN4lEkbcpbI5wGaG0grqZ3IHtV+fEzkboijBncT68xVyWuvDT1T2T8Ml9yhwfhZIJsBLJWMg46jd+mhfjYNlWF4lNfYJJPPVOfZJsPLg1rY/nZgsF8zqB0hAp/5jm3sw74J5+Qz/fd3/i3AAI8FH+M/OgteAAAAAElFTkSuQmCC) no-repeat !important; }
	.plenty-OrderMarking-10 { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAmBJREFUeNqck89rE0EUx78zu5sfkqTGxh/FFqFVEaEIhVrEgyB41EOkNyn0Lnjw4N+Qo+C90Ks0B4/ePOjBgpeCqKRSrRg1aZN0k02yszPje5ttTdGTC19Y3rzvZ957MyM27mL8c0k3SfdJ10lzSXyb9Ja0QXpNisYNKL+wqN4TU/T7RDjuysnpy8XS7DzyUyO/X98uNT9vLbW/fXpgdbROoQp56uSBSCpg8zPHS5UvXF3A2flbyOSLcDK5eFEPuhj4LfzceoUv799Bq7BK4YekOlfg8M6O65Wv3LiD0tw1pF0JR3UgVSsGGEh4WQ+phdvITkziw5uXZR2pXVp6HPcspLM6PXsJxTPnken/gictHAEIMerTWqqCJIyIczj3a+3jqjW6yoDlE8XThVPnZpAe7sNTEVxyszfxwx7+EyVtXHBuc2+/0Nv7scyAxfxEEZ4ZQgYDSIoI+ceMMZA0JJq/R5WwhwCLDLiYoinI0Ie0GtI7bh6HSJZiOWAPe+Nj1P0DmIyC1YaEfxOSWVgCmFCSp390D2pB158M0xG1KGCMhZR/M3gOhlrQSiDsWwTdGFBjwGa3FywFWYOcpNIYwC2J40OkMCKqbkiAINDo9ga8tMmA50pFK22/X8jSALzMqC5XjCA4NJMGNECffG1fQSl9wF43udtrjZ5+xOcPLZFLASlqw5EjAI0GIakbAs3AoNEzHF5jLwN4bBUa0Mx335RDOuuJNJDlKhJARPl92r0zZIDlYfJVriDebvSY6ny3aeFpo2dbO22LnY7Fro9Y/M8xXuMczk08R4/pv5/zbwEGADp6FamzPBRGAAAAAElFTkSuQmCC) no-repeat !important; }
	.plenty-OrderMarking-11 { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAmJJREFUeNqcUz1oFEEYfTOzu3dJzsNTPHPEgJjkwCIgwvmDhZ2VNpG0gRR29vZ2KQULCytbSRpLKwubHIhgZTBiYbxwhAu3t7d7+zMzft9cLtyBlbO84dtvvvfmzZ/Yx2NMNY/wgPCUcIewcpY/JOwTdgmfCcWEIFigZT+gLZ406P+F8NSWbtZr1fUbmF9puKL4sIPw20+og+6pLfQ7Su0Qp0OcsQA1rnwdBXbj0u0m6g9voVy7AFWZcwI6SjA6HaD76St6Xw5QycQepZ8TOuoZmoqClwPfbi89amHx/jrmvQBBouH3U3hhCpVR7PlYuL4ItVDGya+jmyUjeLkf3ZqFktsX1xqoLV1FuTuEbyUUBH3jZtkF9UIYVzNcuwbzvbNttdljgc3Rlfnq0nIDpV4Cv5DwhHT0aQHBvTUoeSRCtUe9frV0HG2yQKtcq8JPNeRoiMncYupoJrEkEUlefKHBHHsctVhgVfLgIIXUGrPmZ0UmAlIVkG5ZWPXcLocxTB6QQ59K1NScs826T8PIHDqJzy/Oj2yQXM6KAFqUYayC/IcH3gfDdLKf2RGyJEFAXBZoZ3FyNzYeKsoisD4JyBmRCbmgPhU5Yh0jG7GAbLPA+0oht/pJVJ3zAR8lZ8wjujyTGJMtRtQPkKKfR6hoGTKXL9JvCupDW9wrjIZwp0VWCYUxyCmXEmKTo29SnBQxxQVNo94Q76033kzs0EEuhybfyOmsq2RujjbTc3sNZz2hshAZUqtp3OervMNcV8EPg+82DbwqWXl6YhN0bIw/GDpwzDke4xquPeOcP6b/fs5/BRgAmNIaCB7B67EAAAAASUVORK5CYII=) no-repeat !important; }
	.plenty-OrderMarking-12 { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAjBJREFUeNqcU0FLG0EUfrM7K9HIQqw5SAwIVksPXgQr4sGbP8DipRQh90KPvfeWY6F3odBeSv0DPXnopYIXT4oBQWyCoUmzm+zGzc5Ov7c7Kdulpw58MLz3vm++N/NGfKS/lgR2gefAM2DVxFvAd+AL8A2I8wR6oTV9EmIJ2zdCyqPy+nqlurFB7mrG91qtxe7Fxfbo6uqljuMPCDXBaYNDwjhg8ns1M3NQ39yk2t4ezVYqJOfn02Q8HFLY79Pd6Sndnp+THUUnCL8C2ja82ti8VY7TeLK/T8s7OzQrJckwJHswIMvzyIoichBzV1aoVC5T9+bmqZUk7P5r2rOw7cbS2hot1Gok7+/JRksWEsL0KQwkLHMN13YvLxtaqRMWOHSqVXehXifZ65EVxySEoOLiiAVhCSdc+6vXc6NO55AFtsrc78MD0XicFmpD0jkBnXPETpgDgS0WeJw+he8TKZUW6gKZioK2nT3flBvjotRkQipJKMmdVFycUwzLohiXPJ2D69D3H0XoPYa1BH2Kf4hoI8A1EWrCTOCaBc6CINge4fQ5WHN0ZjT/ClPyBIggMEKrAe4LNWcs8Bk3f+SFoVtynJSojbW8AM/umK+KJxPtWkp5zJVmto8HSr2WOF2jP54/h7IJI9M3nz4E+nA6AJA/Zq40+SYC9Z9JcsD9BQiUph+lcLqPPGp5lJvpfZrP1ObZRuLdWOt+B0U/AA4yeM8xznEN1xrOn8/039/5twADAGAL+xW9URhJAAAAAElFTkSuQmCC) no-repeat !important; }
	.plenty-OrderMarking-13 { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAnBJREFUeNqcU01rE1EUPfNmJv2SmjRJochItVYpRRGlFinqzh+gdCeFbnQjuHShbnTlUnDjquC26B9w5cKNBbEKQrWR1BZCY5rEaZpMZua9673TaW3pzjcceNx7zplzH+9Zd/EYB5bDmGHcZlxhjKX1EuMj4w3jAyM+KMAreoZ71pMR3j5Ujpo7e248N3rew/BYPiFVS1uF8tf16e8rP+6Y2Lzm0nPWVFgDK00g4pfZnvyti5cuYOzGSfRne5E5lvgjbMVoNwOU3v/C509f0OxuveXyfUbFvozrNm+eZjND89duzuD0VQ99Th9U4IL+KJCvYIU2XMdBbvQ4cgNDqJZrE4HuiPs7JTMrW81Pjk+icIIjV12YigOq2qBaCt5LTXrCEa5oRCsus96wN1j0ikCdibGCsSwQDi+CBUP8P8eGcL26N7hWWZsVg6l8rgC76yAOAJuJcULflf0zIJikxxzLgWjYYEoMzvSgD3qboDWgEyodEu8twzWmQNsE0Yg2OebQjxBFGrHRnMBApf8/amA4gUakNMJOtH8PVv1tP5+Ni8hYGopMMoZ15AwknUHEnIBC+B1fyqtisNRo16cLZgQ9dj8buInAOpBjdyAW89e1umjrNhpBXVpLYrBYizbnijQy6Lq9LLOTWW0Ot2exO3vM8gAt/ppRHbV4UyIsOundXtjQ5Qcu9cIoQj8GWJ5hE5UY6GT2EG3swDcNbJiylBdEKzdREi6HCE610ZqwiRPwOcQUIaQuutRBh3bQIh8N+o11KmGbmnKVHzF8lT6mitxtbrxYMcuNn/QtIW5iI4HspSY94Qg31ew/pv9+zn8FGADx3SpbXdAjPwAAAABJRU5ErkJggg==) no-repeat !important; }
	.plenty-OrderMarking-14 { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAmJJREFUeNqcU01rE1EUPe/NJI1NGxsbRQ1Faysi4kKhFnGhK7dCpTspZC+4dOnHrkvBtQW3xf4BVy7cWHDjRiFqFWusbdpk0iSTzLz3PHcmtUV045CTeXPvOed93avw8BkOPD5xjbhNXCGmBvGPxBviBfGaiA8K4B5UoB4tneDwvq/1wrmjY8WLJ49jaryQqutB6d33H7MfNht3YmufM7RITY0aqMEKRPx0yPPmLk9P4vqZMsaGcxjJJP7YjWI0OiFefVrH2+pn9IxZYfguUfNw45bHweOs9io3L13A1cky/NwwQu2j6TQCoq88+H4Gp4ujyOfzWNvYOm+cE/eXyZ49pSpnT02gXDqCn8jCWQkr/nS6UWcBqxlxCUe479e+VGiyIsz5Y6P5wkRpHNs0jYWoKHYq8UgN5Ft8NHxyEm59q1ALdufFYKZYOIye5yM0/DIpGUr+3P79yNAyR44iVzQ0mBGDaXB/LSaMSQlwDn99bDqBJxxqRJscc9CPEEUGNiJDC9Q/DJgjR5PbpWavDqqtdnc87vWhhmI4TXtP9v+HiazKGKiYHHK77a5Eq2Kw2mkHs7bdgZfN87yy+zewZ+JcehOcWYV9GHLDdiCZVTFYjuJ4wTUbhUw2x+WzLCzfHt9a7y+dsyMKgXYLUbOBOI7FYdkf1PaSadbvuUyWR8DZDo0AmUxqlhiImHvu7sIGO7DNukSXRCsGJqltuAm7VZtz/R6QZw8MySrSUoZh7/Rk9gAu2OaNOinlRcnoQTPVpLaZeGKD+o7b+Aq38Q3YXE8gY4klOXISbqr53Uz/3c6/BBgACgMhdFXU81wAAAAASUVORK5CYII=) no-repeat !important; }
	.plenty-OrderMarking-15 { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAlZJREFUeNqcU01oE0EYfTO7SVoTY0OVtmhAWysoFUGopXjw5sHeKr1JIUdB8Ojdm0dB9FjwKvbmyZMHLxYEEQSlFqHW1ApJmmw2yWZ2xvdt1hr/Lg772OHb995837ffKFx7iKHlE5eJ68QlYiaNfyReEU+Jl4QZFsA9uwm19GiK2zu+p1fPnCiVzk9PYGZqbKCuNo6+3fq68OFz/YaJ7WOG7lFTpQYqzUDED7KeWr54bhpXLpRRKoyiMJr4I+gY1IMOXrzZxut3W4hit87wLaLqYXbJ4+ZuxlOVq4tzWJwrw8+OomN97PcUmkTkPPi+j5OTReTzh/Bp59tZ65Lsnyc1e1pVZk+VcXxiHHuhD6c1oNQASY2O0FA0FY5w329tV2Lr1sVg5dhYvlieHEetq2FES0C5od66wWPZNKuRcGu14m4tWBGD+dKRAnp077bTtmo3EP2+7KD/koloaDAv9NPQGbR6DrGVI5ww8NclpRgLr883NaJN2twMDfo5AxuLQUwD/MMgTgx0ZNAJzcEcbLbC9rjJRSzbkJP52bw/DCyUIacT0UDqxaYYbITtcMHmQngYgcsw5LkfnRxqIrOLDVS/izgM2a9QPmyIwRNjzGonaBQzKstGMeTzrf1ff6NlyiYCegH6QYNepilaP53ttbhdv+1UBpoTAj/PLLJpFkhPp9i0YcMGbLsu5muiFYNYZpuBsg32lp0Qc4dpMMIsvPT3kRJ3eXqL9TdEvJ5oGNXpZaoms63Ufdfdr7vmF7jWDi/BbgLZJzF+E45wU83BZfrv6/xdgAEAJbwYszWVBRkAAAAASUVORK5CYII=) no-repeat !important; }
	.plenty-OrderMarking-16 { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAltJREFUeNqcUz1vE0EQfXt7Z4wdHJKIiMREQTIWShEhRQoBIUTHDzBKhyK5p6enS4lEQUEFdIj8ASoKCoiUJg0IIxERxwKs2LmL7853+8HMnUMSoGKlJ+3OzHs7Mzsr8OI6TiyXcItwj8CO2sj+hfCB8JrwjqCOCIIF7P33EC9XZuj80BVy7er4/MTihTpqlUs529/F9s/P+HSw01NWPyfTOnE6xMkFaDH5yRm4jaX5RdypLuF8sYIxt5QJHKoQ/djH2/YWtna2MYTaIPMDQkeiUZW0eVSAbN5duI2b1WtwSx5iV+HACeE7ERKp4XouLp+bRblUxtfutwUNy+W+yWqWwmnWZ2uoTl7Ej0JALsHFAY7ICzWW9mSyNoupV2v4uNdqams2WGB1+uxkZW5qBvtuCOVYCCFzgeNOZcs6lAmJZ7G9/Uon7K6ywPLE2DiGrkYMn4IdZNdZ5Di5hOF0ICiWOSSwzAJX4DkIEEHzzZB/E0+JaIrQYA5zWQB+GiLVEsbQ0XD64t9k7oXRcLRCRJyjwWkFcTClEgmhCrDaycv4U4Oz0pS+MrBJgigO2Npigc3wMFwxoQdZJmdB5gKOOBaxo9tZINHQYYT4MMtgkwVepUat2f6g4hW5BR5QJBEpTj+jJsRUe5Ai7Q+gjPKZy4O0S5tpG6U3zKh/lAeVanKkGmZIiBSMn0D1YphuxH16SqHPOAOdzbYj5sz3sGGH9FQVN8/CHWWgRrf7CrYf0zwIHuV15mZvwR+DZ5scj40/7Nl2CNseAHtxBt6zLfNRTBabc35/pv/+zr8EGADB3BxoKNN12wAAAABJRU5ErkJggg==) no-repeat !important; }
	.plenty-OrderMarking-17 { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAmJJREFUeNqckz9s00AUxj/HTkxJm9alRVRqK6KilhQ6tKi0CCHEgsQAA1XXSuzsDGVig5mBgYkdBlYmJJCASiBUARJqRKQW0oUkdmLH/+6O75IAlQgMnPyTfXf+Pr/3fM8YuYqDwyLnyRo5S2Z662XyhjwmL0n6U2Bog/pTBeeaMcH5Lcu0NhZKs86pmQUUJ7r6L9UyPpS3sf3pcz0V6SMu3aWmSk3XgEOL7xdGc9dXFpewOn8Rw3kHh3ODnc0gbsH163j18Tlev3sLrxY/4fJNUjUPzcHkw52Ck71x5dJlLM2dQ9a2kKCNlnDhC4/xxrAsC5PjxzHi5LG7XylFodTpPtMGF8yMeW/xzKx9sngaodFCWzURSh+RDDqEwkcgWrwHGBoYpp2P6l5tXin1QrusT02PFyaPTcFLa1D8niGYm6Er1KuU4qWRCkbKSPhudbpWqFT217XB8tiYw5AjNKIQGUldBn2H4p5MCJ21hgbL2uCEabNQaRNIBLTWMP5ioLoGSE1ojdZqA7RCD2aY8Mv0Vv+OQNFAhRlq2r8Ozo7rNo+YQymyFnPXCR/MH7/r0KlFbCAJFVy3Y7CjDbaajWDFHpXIWSZTYKFM9C+iYAqhgTgQaDZCvbOlf6MfBXLNHhR2JqugDMEiEb4tZA9BUhILJBQH9Qjfd3lAgNvaYI8PR0WiVi1b0kBRzLuUkBpBEok0Im2JyEvhftVzPKDuoTbQ2b3nQlFEqmRmGAXjlQmJSaQgmHPaVohdBe+bQtuDPsqbxPujmcgG28DJ5oHsQLcICcWJD7Ad6pz2bab/bucfAgwAb+1H9dOsASYAAAAASUVORK5CYII=) no-repeat !important; }
	.plenty-OrderMarking-18 { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAfZJREFUeNqckzuLWlEUhfd9KIiixERxioAYFVLYCJOA+RcT0g0Dg5aBlOnTTRlIKQykDckfSJ0mKS2diKD4huCo+Nbs73CvXMJUObDGwz5rrbP2nH2tarUqgeUqXileK14onnn134qfiq+KH4pdUCD1el1qtdqZbt/btn2Vy+UeZbNZSafThjQajZ602+2XrVbr8nA4fNbSjWr6qhHLS4D4UywWuyiVSlIoFCQajUo4HDYGm81GFouFNJtNaTQaMp/Pv2n5raLvlMtlRzcfVHxdqVQkn89LJBIRy7JEbzNgHwqFJJlMSiKRkOFw+FxNSf/dpmeNfV0sFiWVShnBdruV3W4n+/3egD01zuDARWO0+udNJpOJ0y8EX4QgCL8OBy4atMQ4J5o6GiJxIfIbXMfj0QAOXDS9Xu8cgzz9ERMghPDQ4nafhwatecb1em3+0xQx+Pf2YAoSwEXjz8HdbDZ7HI/HTwYkeKgFEiBerVaiGsp3ZP01nU7NO/tJ/JjBVwD+zXDRoMXgy70uCjogxj1oEhRzBgcuGrSuN9u34/H4HS0QlUFyXffUBjVMlsslQlEu5Vu0GOyZbT18qs9ywU3+GDuOYwxowx/nyWSCEaN8w5HtfUx9ZlsPPnY6nT/dblcGg4EhA/bUOIMD19OcPqb//pz/CjAAKd9zwUcQBpkAAAAASUVORK5CYII=) no-repeat !important; }
	.plenty-OrderMarking-19 { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAjVJREFUeNqcU01rE1EUvfOVVhMGU2mK0EWktSFIVqVqKXTZH6B0o1DI3r0Ld+5cCi5cuBJcif0D3RXcWLqQKqHQQBbCtKnp0LxOMh/vzXjumzHQUkEccsjjvnPO/Xjv2U/p0mcDa8AT4AGwUMS7wFfgM/AFkH8EBht8zDJ6Zhh3sHxh2fZWvdGo1lstmlvI9SfdLvUODqh3eOgrKT8g9BoaD5rcAB+L396cmnp8f3mZmuvrVK5WabpS0ZvhxQUFvk+d3V36sb9PoyjaRvg54FktIguLVzdKpfbaxgY1Vldp2kYnYUjy/JzkcEgUx+Q4Ds3W6+SWy+T1ek2pFLe7o3s2Lat9b2mJZufnKen3KUVLBvdX9JkVUCiZOcz93um0U6W22WCzVqu5c9iQZ2cYj9TE6wzYWKI65vYHA/fY8zbZYOXWzIwuMzo9pbQQGpdPZ2KS6NEbxBoYrLDBosPnIgTFSmmSSdd/aWEgLYucPLRo6yljUBFKN9L0nwwi06RwNJpcnCMhxO0KspsojdCn+ZcW2CAGZwyOyA2O2GBPBMFDF9ltPj5sWlfmMDkFzg6DANWK8Zi39tjgU5AkW4LItUslTXSKNowr2bl8ziswcGiGrOWL9BOLWpxlj+w0LZpN8cuhgASIgREgkoR+oV1U8w7M92zACb4hcDfMsia3wEKJ/wSAMUWAFgMDNssyvsovgaEeOD8MvtvYeHOilH8MUh/wIWTwmmO8xxzmFprJY/rv5/xbgAEAuAM07pUIOO0AAAAASUVORK5CYII=) no-repeat !important; }
	.plenty-OrderMarking-20 { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAc5JREFUeNqcU0tLAlEUPvMQFTTQEhkUIcxHoOBGBfsVFoKrwH39gnat2wTtW7qI7Ae0zkWtWunCRHAhiDi+8IWPzncZRUJq6MI3c+fc7/vuOXfOlfL5PO0MlXHGuGCkGEEj/sV4Zzwz3hiLjUDGo1gs4qUx7hRFeRkMBtcOhyMdDoePAMwRwxo44BoakowMIH6oVqvnTqeTMpkMuVwuYqEgjUYj0nWdyuUyDYdDikajJQ5fMVpKLBZTeHJbqVQKfr+fUqkUqapKk8mE+v0+8c40n89FLBAI0Hg8pnq9furxeFDuq6hZluXCer0mn89H7XabMN83JEkSnGazSdCsVqsSDHKNRuNA0zTqdru0WCwEcd+AMTIBFxrOKAeDJBZmsxlNp1MyM7CBkWUSBieoC3Uul0tTBvw3RKbQwkAcFIJckykDrn+7GQxqnM4hfs9Oan+mb7fb8VmDwYfNZkt3Oh2RhRkD7I4+gRYGT4lE4hKnatRlarBmAK1s9PZjMBgUBwmT3wAOuNBAi05Ezp9er/eYD/G01+sJIkr5CYvFQpFIhOLxOFr5Bue/uUwt9DYv3GezWT0UCpHb7Sar1SqAOWJYAwdcQ7O9TP++zt8CDAD3I/YDKaHetgAAAABJRU5ErkJggg==) no-repeat !important; }
	.plenty-OrderMarking-21 { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAHXSURBVDjLzZNNi9pQFIbzA+YXDP0zLV3Nb3E9a3d1JQh+g7oQLaKCimL8QGKiMdF0OjUTjB+N0fi9Ghim7aa8vScwglBKabvohZfccM95zntObjgA3N+I+2cARVGuJEnydNjief5LpVLpFAoFTyaTufotgCiKtw8POizrMzaOjfnMhCz3kUgkbn8JkGX5utvtelut1telNYf+ScPHDzL0+yEW8wnC4fCT3+/3+Hy+nzrhBEHwTiYTvCRrQwma2sVIFXCnDaAqA7TbbdRqtcdSqZTIZrOvLwCNRsNY2RbGrKI2FN1kddCB3OtAFAU4joPT6YTj8cjas5DP58epVOrtGcCGZVD1+zuFJYusYh/9noQe03a7xW63w3q9drXf77FYLPCerTOA7b00LMMYYzRS3YDD4eCKksmBbdtYLpfuk5zkcrnvyWSyFAwG33DMzjUblJcNymDtfKMAqkbBlEwu6J0AJNoT3DRNRKPR6sVE2RUwCUCJq9XKDd5sNmfAixOaBbUTj8efLwD1ev3dbDZzDymR9tQSuSAgfa3pdOqe6boO1gJ/AWA371W1Wg00m801gznlcpkvFoutdDp9CoVCx1gsJjFpkUjkORAI8KztG+7/+Zn+VD8AV2IaSQGFiWoAAAAASUVORK5CYII=) no-repeat !important; }
	.plenty-OrderMarking-22 { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJISURBVDjLpZPfa1JhGMftuqvC41l1Hf0JrTtvuyropp8QOdCFWYJgoNC0yOwiiTFtYGCIhBXTs61yc7ZdzAhHiLpTFi21+fP4Y66ZLld9e1/rCIdVBLt4eJ/n8H6+3+9zDkcGQLaTkgzt+YEDYt+KsI/Efj3M7v4vgdaLgeMEbG/Msfs+h1nQZ83ZX+c/BQh0aCPCCrSn4Pos++NL8gzWZtj3jZCiJ1B7pghXnyp2/TUBiVmjbhTcKo+ju3ob3cJdEEgQphWoTCkm/5iAgCoKErexzoer+Jq7ic7bi+jwF7D5Tofup1toLp1AiWNUxSBzuBBg9mxLQGKyjchB4jhK4GF0ls+jkzqHdvIUmYfQyV5HPsB8W52Qn96WgOx2jMRstJaHifuN3/BZAp9E5fUV8C/HsBDh8Jx7sDX15F7Q5/MpJQJkv71kP2V5klnr5u9g880Q2gkKX8arhYfIZDKo1WqoVqtIp9Pw3HfxLpdLKVmhyDHXCkEGwpIKmZQPsUUO85Fp5HI5NBoNCIKASqWCer2OZDIJh8MxLhHITzCj9EzNXMLKykrPkV6mZ7lcRqlU6hXtqaDNZvtusVg8JpNpsL9L9rH86OKctx+XOoogrWKx2CtRJBaLwWAwePoCH/3yI6FQiKewKECj06KQWGISaqTT6ZqST8Jx3AjdkV6gbqlUColEou8ej8d7MzWIRqPQaDQeiYDf79/v9XpH3G4373Q6efKyPHa73Wu1WrNmszlrNBoDer0+pNVqm2q12qNSqQZlO/2dfwL4RvrQAqV2MgAAAABJRU5ErkJggg==) no-repeat !important; }
	.plenty-OrderMarking-23 { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAITSURBVDjLlZLNi1JhFMaHiP6AvN0Ls4vob2jauYiIFoGBtnFlG1fSws1gDBdXLhIhdaEuvILfjtfK0cyvRflBeHWUEBW1nHGKpmkcpWw+ani6r9DCutn0wuGFl/P8znPOeZcALP0ekUhE7vf7HR6PZ+ByuQZ2u91hsVjkUrl/PITDYbnP5xMajQb6/T46nQ5KpRJMJpNgNBrlkoB4PL7M8zwbCoWaXq93RMStVguVSgXlchmCICCXy8FgMDgkAdFolK1Wq+j1emi326jX6ygUCsjn80ilUkgkEigWi9Dr9ac6nY7TarUrc4BAINDsdruo1WpzQtEZRDiCwSDE1pDJZBCLxaDRaLg5gDispnhmvRKrJJFU/SUWBwqO4+B2u5HNZqFWq8dzAKfTyRIh6ZVAksnkrDpxkk6nIW4F4nxmrghMpVLNO7Barctms5m12Wx46bw23XRf/TF5JsP4qQwHT2QYRWXYW7+Ix6vXT5VKJadQKFYk1/g1x5z/kmUU0+otnHy04Hi4hu8HHD4n6elegr7/z38wyTA3xy+Y7mHvAb69UWDauI0PiSuQEkoCRil663CwhuMddlad3EfbD/F+4zIWAvaf0+dEm48+bdDYjdMYC3dn4snmvYViya9MYoe/NNx/fQdb69R4EKGYMwOGPHVhO0qt7r66gXdhKrJIKAkQq6nehqijflCmOov4ry38T/wEpFjkJMz8PioAAAAASUVORK5CYII=) no-repeat !important; }
	.plenty-OrderMarking-24 { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIESURBVDjLvVO/a9tAFD5ZdWwZg11w2iR1idMhxhm8ZOlS3K1rlwwZRD2EQCd7KIUOpmvoj7E4BBxElw72kMFLh5osBkEp3fwXlMY4TlRJtk4/7kffqW6wcDMFevDxTu/u+/S9d3cS5xzdZMTQDcet6xY6nc7jIAh2AU9830eAz4BP9Xr9dH6f9K8S2u22IL8rFovb6XQaEULQeDxGuq5/A5EXjUbjdMFBt9tdA9I+YAewWiqVbieTSWRZVigg5uVyebvf7+/C9kUBUN7P5/OvM5kMopQiz/OQYRhoZj/MpVIpkd+r1WoJyB02m019XmBH2J1OpwhjfEUEN1fRtm1UqVRipmk+6/V6ghYRCCHIruuGfxQk4URE8S3WJ5MJyuVyYv40coywsT0cDv+cbSyGHMcJhRRFCcEYQ5IkoWw2i0ajkRA4ifQABI4Gg0FYyszNV4AMeDQr4TtAATwEnEBjDxeOsaadvYnJSGEUYRFdj2PGmTLxOSaEKZ7LMCVccWzy8svBJo6U8Pz458pWPlF1A97aXE1UL2zS2rgbr54bQevBnXj114XfKkDevPQO/pIjDuofz94TymU3YNQnXMYeozRgUAKjxGdyABH6KLsOfaV/2MKRt7B39OPe+nJcPbeIVlheUg0j0AorS6p5GWj31xKqZRJtfSOlAvntPPnaq/xfX+NvE6ltVjnyz4AAAAAASUVORK5CYII=) no-repeat !important; }
	.plenty-OrderMarking-25 { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAADnSURBVCjPY/jPgB8ykKTggMPemTsfbHmwfuZqBywKDjjsOXP//6f/H/9f/7/0zAIHJAXHpQ437L+6+93j/9//fwHDC/9nz0RScKjhBlDf5/9fgRhkwgcgnvZ34oIeC6iCvVffwyXf/3/3/y0Qvvt//H/7AqiCnVffAqU/wqXf/H/9/9X/l/+bP0AVbG449f/F/6f/z/0/AyQf/z/9/yyQf+B/HcyEdVIrG5ZcXXB19oJpiyc96H3Qub51R+OH2gVVFnBv+uj6+ProYtIIBb6f/08v8OFHp9FMQMdoIYmpG10Bhv1ExSYAuRQri6NL7YwAAAAASUVORK5CYII=) no-repeat !important; }
	.plenty-OrderMarking-26 { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIBSURBVDjLpZPdS9NRGMe98zLw/H47QxDCLoqgC/+E/oJpL1qtNxMlRkhIYO7CoJuwQLqILoPwpWnkluKknNWVXVTWXnzZ3Ku65ftsm7/z+w3p23NGQbFZji4enodz+DzP9/twTgWAiv+JkofinblS5t235vbdN2a17AYEQebcFEfWw+/I+tskxz8bEOj5DQwI/0UQOL7zmu+lX/FNebc1YTq66TbVlGyQneLfMx6OzCQP5VOPIEMPd0JP3AOBLgKxMW5CkQKaxgm8JWuahvzXxwR2QczboC/cgBGxQ4t0Y23MhH0tSJBkIue1wojfhZhthQg0Q/gvU1yCEbVj52MjUi4VSaeK5RG1ssgCyUwXZNNUEbhaADXfeWjes6TmGnLBDgIVZ5EC8uaW3jIzF5BP9kLMtUEuUPOdI/gMtC8WUmQlG7ex8d6C+HMFsWGlqkjB6qj6MOu/Dj3YTqCETxdgzVtPe7iJ9WkLokNKS8TB2sIOdviPBqmXqjVJ/rY/NMFYfkBNbKSiCcJ3CvqiDVufriDuqkX4GUPJJa6MqE9kXnqh3E+6jyPtJRvRLhgxO7Y/tyDmrMXiIKsODbC+4AB7uu9LJG9S5pHE6DGQzMTadANCg6yHQAT7meOvL5HAE+TvkKxpWkEqTdMX+lm3rOf7qoYP9Bd+gr+gOoqTZX2mcuIHSo3eNm+GAIoAAAAASUVORK5CYII=) no-repeat !important; }
	.plenty-OrderMarking-27 { background: url(data:image/png;base64,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) no-repeat !important; }
	.plenty-OrderMarking-28 { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIxSURBVDjLpdNdSFNhGAdwKSNyg6ALC8GLoE8so49VRHVRFFQISlJeREXQBxWSSZDUpNrOTDoxSGvhmokV1TazLeekTBda9rVKmW5lYq6slgutOI7j1vn3vKc4rCAv3MXDgfPy/73Pc3hOEoCkROq/B6v2GdIWHLnhmK1v7ZtZ3PIuo9DmOr17iaUkLx1Ud6g2jgqo+JCU4x7Bs5AEe4+EhbYYMsv9iEYGcU+/VEZkYNkew7iJnHBrgl4YSeYEJJcIGF8qoKw9Bv8g8GkY8IaBthDgqatCsNGAq4czGbBLBhbv5VWT+EiL2Q9cfg2YA0DDe+AxBeqDQPvX3+/PdwKmfA0+PDDCuGM6A9JkYP5Byyy1SexgQM5dIJvqpJdCb4DWz8BDKguhhzxDor1Ig+7afBaG8hFnFDiyp1ZFgxa6JbcR2NoEnCLg2ltqfQBwUzcVhJc1+4c8/Br0urV/A9OKvJyxQ/qmfQ5so/D2ZoB7CVh7gN4fwP1+wEWjGK/XoKt6C9rOrWeATwHUugEn3RBjrW9oAI4TdJPCno80RlfsZ27d9+Eslxitcdpk4HbxCgboFEB1JvKk3CfhSjdQTXM7+yRorCLUZ8PSposvvMZX0bydtf2Vi9JT4avcjIr9GQxYrQBzC2zmVG2nkGIISyncF2mKLiDOKbQ+it8JCqy9dGCe3EH8/KMu0j9AqePEyoSAwFNTVkKAHG7i1ykrPCbAfmw5A46OBbjw5y9kz8nxZ78A90vOcDVd+i0AAAAASUVORK5CYII=) no-repeat !important; }
	.plenty-OrderMarking-29 { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIySURBVDjLpdNdTFJhGAdw120X3XXR5kU33fQxS0+5Yl24lFnQKsvl2nJLM0fmXLNASceKgAv8yBGgJPEhkcIShEEYKuKU1IxcTm0WUDiJ1Fpbm1tZ/855186oLS/k4r/34n2e355z9rwZADLSyX8vCm+WU6fqT38+21S4ztPy1rmK4lXF5Ry//Hwm6LjoHN8QOGOgUOe9iGByCJ7FJ5BMX0ORiosfa1/wTHqQIAQ4VCHbwpXL53iWHPAe7QefJAvq4G2MLY9gcnUcQ0kf/AkvAm4DPvhl6Lq+jwEuESD7inLrCWXJ10BygC56SgpHlofxfGUMjvhjDH7sR1e0Hfq3VmiqKSwOt6CldCcD7CDA3qrOXfRo37tjRojC5SRt81KYIxp4lxx0+mCOaqEON8NeR2Ght5ppBvsTT9Yqai60F/y0vTehPlyBW+FKAliiOvQnPGQKY+Q+TOOdCCjzEPU2/A1wxIaH3a8N0C20ouGVAI3TVVC9kcEa0yO0MgrfkptM0mprwqypGKG2AgaYYYEsqfGFI94D4csy1E6VonlWgt64Fb6EG7aYGTdGK1ETEv6yu+wEcDQeZoA7LHBEJfxkiejQQxczccZtEE8JwHNRKLMK1rRzng6R3xU8kLkdM/oidAh2M8BRFsi7W/Iu38wBty8bXCcdSy6OyfjfUneCbjj34OoeMkHq92+4SP8A95wSTlrA/ISGnxZAmgeV+ewKbwqwi3MZQLQZQP3nFTLnttS73y9CuFIqo/imAAAAAElFTkSuQmCC) no-repeat !important; }
	.plenty-OrderMarking-30 { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIrSURBVDjLpdNfSFNhGMdx6Q9RDoIuLAQtYVuWpZSunKQXjYIKwdBqkJkEYRhIBEFRRmVbGYNB2hTnVKaGzWFq/im0mLgyLTNjpdSFJkX/hCxYY26db+ecYliQF3rx44Xz8nzO8748bxgQNp/8d8OoS41s0Ca0uBPXvu3VqMYbk+Parx5Nsl3RRyHmjpjdswKfosOF6ey9CENPEFqdBNM2MaKNJ+D7StflLTIiA8bUrQu8sUuavOrF017lIrwxYqIXErSWwOsR+PgBhgZhoA9XWw0T3UbqTsZLwBEZMKUkhvtUS3uxW6G+GmrEtfsuPH0MXR3gGf79vfIGZQUa3vWYMR+OkYBIGbBpN6r9qxUvZEBsmYMZUHwR6sSiPjf0P4RaG1OnTvidZzS8uV0gFRO6xBaNMiOgXjmB3QY5WZB7AK5dAkc9PBdb7+oUu6pgpLRkymXazlhn4d/AYMIqg2Axf8NQCHnZcCwHTAZodsD4GPTch3vtDJeX88q+n77rOyXAEwK+rFe0in8Iyq1n7oKic9B0C9wugjerf34/lPXDr08PuPJyZKD5fIoEFIUAX2x4v2AthYZaMXaEjlb8Og2TaxTCs317BgMWs/59fm7V5qgIPFWZVOTHSUBaCGhMXmd9GR/hnVQuEz6LGVWt8DuSYh/NnAmxQFd5fIPcwczzzzpI/wDFLRe2zQsYHShLnxcgFz8w7QiN8JwA59lkCTg9F8Dy5xVK6/KZe78AQiW2y4SvvaoAAAAASUVORK5CYII=) no-repeat !important; }
	.plenty-OrderMarking-31 { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIxSURBVDjLpdNdSFNhGAfww0Cri+gyKLowpMC+LsooEy+SgqJuKqRIiIQKkryoi4zaUmbWSHKdPkYz05xdnKNobmwW6Vi6tbk2TDYl82PTTSr3PXe2s2T+O+dgYwV54S7+vBcvz4/neXleAgCRTf570UXdLda9ORUytW1LDbbkp1TK8h8PLu1rvn92C7houBxfEbA/E+Hn4C6wAQMYTxO8vbkwvMjBYiKED3X7BUQAaFqao6XLgxZyDaxyAp9JArYnBCLjd5CM2bDIupCI6MEEtRjQtWK2rx7t13fzQMUfYHNfx7H4wtQ9xFwPEZuuR+I7jWSgH9H5FrBRI4KeGgTcN6CoKoT3YyMaL+TxwCYBoOi6M5+6i37xgM9YICQ8elnAmKCai4YDJHCPnEDnrUJMdFfxxUg/Ik2JlSPq7anYtAw+0x74zXs54AqYGRLxMN9FK/yem5hySpcMDYfh6hX/DXRR15yhcclS2FEBv+Ugl0OIjFWCmVUgGR9FzE8h6mvGF7MMY21lMJNHecCZBrRUWXhhcrn9ga0IOy4Kxey8BoGZWnwbKsCkbSOGX+cJwFtJEQ9I04C+o5SNTojBuOXc3I8Qn1Nh7v062BUiWHXnWLtD+1TVTxt7anPhfHUayqs7eKAkDajbz3tN5HpYH4swJBfBQq7Fu6aSROZOcAWlLyt3Ch1kzr/iIv0DyHpqirMCvloVJ7MChGJ9w5H0Cq8K6Lx9gAeqVwM8X/6F/Lkh8+43zznRPkqpYfEAAAAASUVORK5CYII=) no-repeat !important; }
</style>',
    			'append');
    	}
    }

    /**
     * Register all cronjobs
     */
    protected function registerCronjobs()
    {

        // Export Orders
        $this->createCronJob(
        	'Plentymarkets Order Export',
        	'PlentymarketsOrderExportCron',
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
        	'PlentymarketsOrderIncomingPaymentExportCron',
        	PlentymarketsCronjobController::INTERVAL_EXPORT_ORDER_INCOMING_PAYMENT,
        	true
        );

        $this->subscribeEvent(
        	'Shopware_CronJob_PlentymarketsOrderIncomingPaymentExportCron',
        	'runOrderIncomingPaymentExport'
        );

        // Item Import
        $this->createCronJob(
        	'Plentymarkets Item Import',
        	'PlentymarketsItemImportCron',
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
        	'PlentymarketsItemPriceImportCron',
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
        	'PlentymarketsItemPriceStockCron',
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
        	'PlentymarketsOrderImportCron',
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
        	'PlentymarketsExportCron',
        	PlentymarketsCronjobController::INTERVAL_EXPORT,
        	true
        );

        $this->subscribeEvent(
        	'Shopware_CronJob_PlentymarketsExportCron',
        	'onRunExportCron'
        );

        // Cleanup
        $this->createCronJob(
        	'Plentymarkets Cleanup',
        	'PlentymarketsCleanup',
        	3600,
        	true
        );

        $this->subscribeEvent(
        	'Shopware_CronJob_PlentymarketsCleanup',
        	'onRunCleanupCron'
        );
    }


    /**
     *
     * @param Shopware_Components_Cron_CronJob $Job
     */
	public function onRunCleanupCron(Shopware_Components_Cron_CronJob $Job)
	{
		$dirty = array(
			'plenty_mapping_attribute_group' => array('id', 's_article_configurator_groups'),
			'plenty_mapping_attribute_option' => array('id', 's_article_configurator_options'),
			'plenty_mapping_category' => array('id', 's_categories'),
			'plenty_mapping_country' => array('id', 's_core_countries'),
			'plenty_mapping_currency' => array('currency', 's_core_currencies'),
			'plenty_mapping_customer' => array('id', 's_order_billingaddress'),
			'plenty_mapping_item' => array('id', 's_articles'),
			'plenty_mapping_item_variant' => array('id', 's_articles_details'),
			'plenty_mapping_measure_unit' => array('id', 's_core_units'),
			'plenty_mapping_method_of_payment' => array('id', 's_core_paymentmeans'),
			'plenty_mapping_producer' => array('id', 's_articles_supplier'),
			'plenty_mapping_property' => array('id', 's_filter_options'),
			'plenty_mapping_property_group' => array('id', 's_filter'),
			'plenty_mapping_shipping_profile' => array('id', 's_premium_dispatch'),
			'plenty_mapping_vat' => array('id', 's_core_tax')
		);

		foreach ($dirty as $mappingTable => $target)
		{
			Shopware()->Db()->exec('
				DELETE FROM ' . $mappingTable . ' WHERE shopwareID NOT IN (SELECT ' . $target[0] . ' FROM ' . $target[1] . ');
			');
		}

// 		Shopware()->Db()->exec('
// 			DELETE FROM plenty_log WHERE `timestamp` < FROM_UNIXTIME('. strtotime('-1 week') .')
// 		');
	}

	/**
	 *
	 * @param Shopware_Components_Cron_CronJob $Job
	 */
    public function onRunOrderExportCron(Shopware_Components_Cron_CronJob $Job)
    {
    	PlentymarketsCronjobController::getInstance()->runOrderExport($Job);
    }

    /**
     *
     * @param Shopware_Components_Cron_CronJob $Job
     */
    public function runOrderIncomingPaymentExport(Shopware_Components_Cron_CronJob $Job)
    {
    	PlentymarketsCronjobController::getInstance()->runOrderIncomingPaymentExport($Job);
    }

    /**
     *
     * @param Shopware_Components_Cron_CronJob $Job
     */
    public function onRunOrderImportCron(Shopware_Components_Cron_CronJob $Job)
    {
    	PlentymarketsCronjobController::getInstance()->runOrderImport($Job);
    }

    /**
     *
     * @param Shopware_Components_Cron_CronJob $Job
     */
    public function onRunExportCron(Shopware_Components_Cron_CronJob $Job)
    {
    	PlentymarketsCronjobController::getInstance()->runExport($Job);
    }

    /**
     *
     * @param Shopware_Components_Cron_CronJob $Job
     */
    public function onRunItemImportCron(Shopware_Components_Cron_CronJob $Job)
    {
    	PlentymarketsCronjobController::getInstance()->runItemImport($Job);
    }

    /**
     *
     * @param Shopware_Components_Cron_CronJob $Job
     */
    public function onRunItemPriceImportCron(Shopware_Components_Cron_CronJob $Job)
    {
    	PlentymarketsCronjobController::getInstance()->runItemPriceImport($Job);
    }

    /**
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
    	$OrderResoure = new \Shopware\Components\Api\Resource\Order();
    	$OrderResoure->setManager(Shopware()->Models());

    	$orderId = $OrderResoure->getIdFromNumber($arguments->getSubject()->sOrderNumber);

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
     *
     * @return string
     */
    public function getLabel()
    {
    	return 'plentymarkets';
    }

    /**
     *
     * @return string
     */
    public function getVersion()
    {
    	return '0.0.2';
    }

    /**
     * Returns the informations of plugin as array.
     *
     * @return array
     */
    public function getInfo()
    {
        return array(
			'version' => $this->getVersion(),
			'autor' => 'plentymarkets GmbH',
			'copyright' => 'Copyright © 2013, plentymarkets GmbH',
			'label' => $this->getLabel(),
			'support' => 'http://www.plentymarkets.eu/service-support/',
			'link' => 'http://www.plentymarkets.eu/'
		);
    }

}
