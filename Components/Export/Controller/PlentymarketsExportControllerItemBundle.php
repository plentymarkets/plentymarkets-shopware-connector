<?php
/**
 * Created by IntelliJ IDEA.
 * User: dbaechtle
 * Date: 03.01.14
 * Time: 13:25
 */

require_once PY_SOAP . 'Models/PlentySoapObject/AddItemsBaseItemBase.php';
require_once PY_SOAP . 'Models/PlentySoapObject/Integer.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemAvailability.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemCategory.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemPriceSet.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemTexts.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/AddItemsBase.php';
require_once PY_SOAP . 'Models/PlentySoapObject/Integer.php';
require_once PY_SOAP . 'Models/PlentySoapObject/AddBundle.php';
require_once PY_SOAP . 'Models/PlentySoapObject/AddBundleItem.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/AddItemsToBundle.php';

require_once PY_COMPONENTS . 'Export/Entity/PlentymarketsExportEntityItemBundle.php';

class PlentymarketsExportControllerItemBundle {

	public function __construct()
	{
		PlentymarketsUtils::registerBundleModules();
	}

	public function export()
	{
		$repository = Shopware()->Models()->getRepository('Shopware\CustomModels\Bundle\Bundle');

		/** @var $bundle Shopware\CustomModels\Bundle\Bundle*/
		foreach ($repository->findAll() as $bundle)
		{

			$export = new PlentymarketsExportEntityItemBundle($bundle);
			$export->export();



		}
	}
} 