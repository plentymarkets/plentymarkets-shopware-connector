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

require_once PY_SOAP . 'Models/PlentySoapObject/Integer.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemAttributeMarkup.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemAttributeValueSet.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemAvailability.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemBase.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemCategory.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemFreeTextFields.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemOthers.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemPriceSet.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemProperty.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemStock.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemSupplier.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemTexts.php';
require_once PY_SOAP . 'Models/PlentySoapObject/String.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/GetItemsBase.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemPriceSet.php';
require_once PY_SOAP . 'Models/PlentySoapRequestObject/GetItemsPriceLists.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/GetItemsPriceLists.php';
require_once PY_SOAP . 'Models/PlentySoapObject/GetCurrentStocks.php';
require_once PY_SOAP . 'Models/PlentySoapRequestObject/GetCurrentStocks.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/GetCurrentStocks.php';
require_once PY_SOAP . 'Models/PlentySoapObject/DeliveryCountryVAT.php';
require_once PY_SOAP . 'Models/PlentySoapObject/GetVATConfig.php';
require_once PY_SOAP . 'Models/PlentySoapObject/GetWarehouseList.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/GetWarehouseList.php';
require_once PY_SOAP . 'Models/PlentySoapObject/GetMethodOfPayments.php';
require_once PY_SOAP . 'Models/PlentySoapObject/Integer.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/GetMethodOfPayments.php';
require_once PY_SOAP . 'Models/PlentySoapObject/GetShippingProfiles.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ShippingCharges.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ShippingChargesCosts.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/GetShippingProfiles.php';
require_once PY_SOAP . 'Models/PlentySoapObject/GetMultiShops.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/GetOrderStatusList.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/SearchOrders.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/GetItemsPriceUpdate.php';
require_once PY_SOAP . 'Models/PlentySoapResponseObject/GetItemsPriceUpdate.php';
require_once PY_COMPONENTS . 'Import/PlentymarketsImportItemController.php';
require_once PY_COMPONENTS . 'Import/Entity/PlentymarketsImportEntityItem.php';
require_once PY_COMPONENTS . 'Import/Entity/PlentymarketsImportEntityItemLinked.php';
require_once PY_COMPONENTS . 'Import/Entity/PlentymarketsImportEntityItemStock.php';
require_once PY_COMPONENTS . 'Import/Entity/Order/PlentymarketsImportEntityOrderAbstract.php';
require_once PY_COMPONENTS . 'Import/Entity/Order/PlentymarketsImportEntityOrderIncomingPayments.php';
require_once PY_COMPONENTS . 'Import/Entity/Order/PlentymarketsImportEntityOrderOutgoingItems.php';

/**
 * The class PlentymarketsImportController does the actual import for different cronjobs e.g. in the class PlentymarketsCronjobController.
 * It uses the different import entities in /Import/Entity respectively in /Import/Entity/Order, for example PlentymarketsImportEntityItem.
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportController
{
	
	
	/**
	 * Reads the items of plentymarkets that have changed
	 */
	public static function importItems()
	{
		$PlentymarketsImportItemController = new PlentymarketsImportItemController();
		$PlentymarketsImportItemController->importItems();
	}

	/**
	 * Updates the item prices
	 */
	public static function importItemPrices()
	{
		// Dependencies
		$numberOfPricesUpdates = 0;

		PlentymarketsLogger::getInstance()->message('Sync:Item:Price', 'LastUpdate: ' . date('r', PlentymarketsConfig::getInstance()->getImportItemPriceLastUpdateTimestamp(time())));
		$timestamp = PlentymarketsConfig::getInstance()->getImportItemPriceLastUpdateTimestamp(time());
		$now = time();

		$Request_GetItemsPriceUpdate = new PlentySoapRequest_GetItemsPriceUpdate();
		$Request_GetItemsPriceUpdate->LastUpdateFrom = $timestamp;
		$Request_GetItemsPriceUpdate->Page = 0;

		do
		{
			$Response_GetItemsPriceUpdate = PlentymarketsSoapClient::getInstance()->GetItemsPriceUpdate($Request_GetItemsPriceUpdate);

			$pages = max($Response_GetItemsPriceUpdate->Pages, 1);
			PlentymarketsLogger::getInstance()->message('Sync:Item', 'Page: ' . ($Request_GetItemsPriceUpdate->Page + 1) . '/' . $pages);

			foreach ($Response_GetItemsPriceUpdate->ItemsPriceUpdate->item as $ItemsPriceUpdate)
			{
				$ItemsPriceUpdate instanceof PlentySoapResponseObject_GetItemsPriceUpdate;

				try
				{
					// Base item
					if (preg_match('/\d+\-\d+\-0/', $ItemsPriceUpdate->SKU))
					{
						$sku = explode('-', $ItemsPriceUpdate->SKU);
						$itemID = PlentymarketsMappingController::getItemByPlentyID($sku[0]);
						$PlentymarketsImportEntityItemPrice = new PlentymarketsImportEntityItemPrice($ItemsPriceUpdate);
						$PlentymarketsImportEntityItemPrice->update($itemID);
					}

					// Variant
					else
					{
						$itemDetailID = PlentymarketsMappingController::getItemVariantByPlentyID($ItemsPriceUpdate->SKU);
						$PlentymarketsImportEntityItemPrice = new PlentymarketsImportEntityItemPrice($ItemsPriceUpdate, $ItemsPriceUpdate->Markup);
						$PlentymarketsImportEntityItemPrice->updateVariant($itemDetailID);
					}

					++$numberOfPricesUpdates;
				}
				catch (PlentymarketsMappingExceptionNotExistant $E)
				{
				}
			}
		}

		// Until all pages are received
		while (++$Request_GetItemsPriceUpdate->Page < $Response_GetItemsPriceUpdate->Pages);

		PlentymarketsConfig::getInstance()->setImportItemPriceLastUpdateTimestamp($now);
		PlentymarketsLogger::getInstance()->message('Sync:Item:Price', $numberOfPricesUpdates . ' prices have been updated');
	}

	/**
	 * Updates orders
	 */
	public static function importOrders()
	{
		// Starttimestamp
		$timestsamp = time();
		
		// Get the data from plentymarkets (for every mapped shop)
		$shopIds = Shopware()->Db()->fetchAll('
			SELECT plentyID FROM plenty_mapping_shop
		');
		
		foreach ($shopIds as $shopId)
		{
			$PlentymarketsImportEntityOrderIncomingPayments = new PlentymarketsImportEntityOrderIncomingPayments($shopId['plentyID']);
			$PlentymarketsImportEntityOrderIncomingPayments->import();
	
			$PlentymarketsImportEntityOrderOutgoingItems = new PlentymarketsImportEntityOrderOutgoingItems($shopId['plentyID']);
			$PlentymarketsImportEntityOrderOutgoingItems->import();
		}
		
		PlentymarketsConfig::getInstance()->setImportOrderIncomingPaymentsLastUpdateTimestamp($timestsamp);
		PlentymarketsConfig::getInstance()->setImportOrderOutgoingItemsLastUpdateTimestamp($timestsamp);
	}

	/**
	 * Updates orders
	 */
	/**
	 * Update stocks
	 */
	public static function importItemStocks()
	{
		$Request_GetCurrentStocks = new PlentySoapRequest_GetCurrentStocks();
		$Request_GetCurrentStocks->LastUpdate = PlentymarketsConfig::getInstance()->getImportItemStockLastUpdateTimestamp(0);
		$Request_GetCurrentStocks->Page = 0;
		$Request_GetCurrentStocks->WarehouseID = PlentymarketsConfig::getInstance()->getItemWarehouseID(0);

		PlentymarketsLogger::getInstance()->message('Sync:Item:Stock', 'LastUpdate: ' . date('r', PlentymarketsConfig::getInstance()->getImportItemStockLastUpdateTimestamp(-1)));
		PlentymarketsLogger::getInstance()->message('Sync:Item:Stock', 'WarehouseId: ' . PlentymarketsConfig::getInstance()->getItemWarehouseID(0));

		// Helper
		$timestamp = time();
		$numberOfStocksUpdated = 0;

		do
		{
			$Response_GetCurrentStocks = PlentymarketsSoapClient::getInstance()->GetCurrentStocks($Request_GetCurrentStocks);

			foreach ($Response_GetCurrentStocks->CurrentStocks->item as $CurrentStock)
			{
				$CurrentStock instanceof PlentySoapObject_GetCurrentStocks;
				try
				{
					// Variant
					$itemDetailsID = PlentymarketsMappingController::getItemVariantByPlentyID($CurrentStock->SKU);
				}
				catch (PlentymarketsMappingExceptionNotExistant $E)
				{
					// Master item
					$parts = explode('-', $CurrentStock->SKU);
					try
					{
						$itemID = PlentymarketsMappingController::getItemByPlentyID($parts[0]);
						$Detail = Shopware()->Models()
							->getRepository('Shopware\Models\Article\Detail')
							->findOneBy(array(
								'articleId' => $itemID
						));

						$itemDetailsID = $Detail->getId();
					}
					catch (PlentymarketsMappingExceptionNotExistant $E)
					{
						continue;
					}
				}
				
				// Book
				PlentymarketsImportEntityItemStock::update($itemDetailsID, $CurrentStock->NetStock);
				
				// Count
				++$numberOfStocksUpdated;
			}
		}

		// Until all pages are received
		while (++$Request_GetCurrentStocks->Page < $Response_GetCurrentStocks->Pages);

		//
		PlentymarketsLogger::getInstance()->message('Sync:Item:Stock', $numberOfStocksUpdated . ' stocks have been updated');
		PlentymarketsConfig::getInstance()->setImportItemStockLastUpdateTimestamp($timestamp);
	}

	/**
	 * Fetches the methods of payments
	 *
	 * @return array
	 */
	public static function getMethodOfPaymentList()
	{
		$timestamp = PlentymarketsConfig::getInstance()->getMiscMethodsOfPaymentLastImport(0);
		if (date('dmY') == date('dmY', $timestamp))
		{
			return unserialize(PlentymarketsConfig::getInstance()->getMiscMethodsOfPaymentSerialized());
		}

		$Request_GetMethodOfPayments = new PlentySoapRequest_GetMethodOfPayments();
		$Request_GetMethodOfPayments->ActivMethodOfPayments = true;

		// Do the request
		$Response_GetMethodOfPayments = PlentymarketsSoapClient::getInstance()->GetMethodOfPayments($Request_GetMethodOfPayments);
		
		// The call wasn't successful
		if (!$Response_GetMethodOfPayments->Success)
		{
			// Write to log
			PlentymarketsLogger::getInstance()->error('Import:MethodOfPayment', 'Methods of payment could not be retrieved');
				
			// Old data should be available
			if ($timestamp != 0)
			{
				return unserialize(PlentymarketsConfig::getInstance()->getMiscMethodsOfPaymentSerialized());
			}
				
			// Otherwise return an empty array
			return array();
		}
		
		$where = 'plentyID NOT IN (';
		
		// Prepare data
		$methodOfPayments = array();
		$methodOfPaymentsIDs = array();
		foreach ($Response_GetMethodOfPayments->MethodOfPayment->item as $MethodOfPayment)
		{
			$MethodOfPayment instanceof PlentySoapObject_GetMethodOfPayments;
			$methodOfPayments[$MethodOfPayment->MethodOfPaymentID] = array(
				'id' => (integer) $MethodOfPayment->MethodOfPaymentID,
				'name' => $MethodOfPayment->Name
			);
			
			$methodOfPaymentsIDs[] = $MethodOfPayment->MethodOfPaymentID;
		}
		
		$where .= implode(',', $methodOfPaymentsIDs).')';
		
		// Delete non active plentymarkets MOPs from mapping table:
		$affectedRows = Shopware()->Db()->delete('plenty_mapping_method_of_payment', $where);	

		PlentymarketsConfig::getInstance()->setMiscMethodsOfPaymentLastImport(time());
		PlentymarketsConfig::getInstance()->setMiscMethodsOfPaymentSerialized(serialize($methodOfPayments));

		return $methodOfPayments;
	}

	/**
	 *
	 * @return array
	 */
	public static function getOrderStatusList()
	{
		$timestamp = PlentymarketsConfig::getInstance()->getMiscOrderStatusLastImport(0);
		if (date('dmY') == date('dmY', $timestamp))
		{
			return unserialize(PlentymarketsConfig::getInstance()->getMiscOrderStatusSerialized());
		}

		$Request_GetOrderStatusList = new PlentySoapRequest_GetOrderStatusList();
		$Request_GetOrderStatusList->Lang = 'de';

		// Do the request
		$Response_GetOrderStatusList = PlentymarketsSoapClient::getInstance()->GetOrderStatusList($Request_GetOrderStatusList);

		// The call wasn't successful
		if (!$Response_GetOrderStatusList->Success)
		{
			// Write to log
			PlentymarketsLogger::getInstance()->error('Import:Order:Status', 'Sales order statuses could not be retrieved');
		
			// Old data should be available
			if ($timestamp != 0)
			{
				return unserialize(PlentymarketsConfig::getInstance()->getMiscOrderStatusSerialized());
			}
		
			// Otherwise return an empty array
			return array();
		}
		
		// Prepare data
		$orderStatusList = array();
		foreach ($Response_GetOrderStatusList->OrderStatus->item as $OrderStatus)
		{
			$OrderStatus instanceof PlentySoapObject_GetOrderStatus;
			$orderStatusList[(string) $OrderStatus->OrderStatus] = array(
				'status' => (string) $OrderStatus->OrderStatus,
				'name' => $OrderStatus->OrderStatusName
			);
		}

		uksort($orderStatusList, 'strnatcmp');

		PlentymarketsConfig::getInstance()->setMiscOrderStatusLastImport(time());
		PlentymarketsConfig::getInstance()->setMiscOrderStatusSerialized(serialize($orderStatusList));

		return $orderStatusList;
	}

	/**
	 *
	 * @return array
	 */
	public static function getOrderReferrerList()
	{
		$timestamp = PlentymarketsConfig::getInstance()->getMiscSalesOrderReferrerLastImport(0);
		if (date('dmY') == date('dmY', $timestamp))
		{
			return unserialize(PlentymarketsConfig::getInstance()->getMiscSalesOrderReferrerSerialized());
		}

		// Do the request
		$Response_GetSalesOrderReferrerList = PlentymarketsSoapClient::getInstance()->GetSalesOrderReferrer();

		// The call wasn't successful
		if (!$Response_GetSalesOrderReferrerList->Success)
		{
			// Write to log
			PlentymarketsLogger::getInstance()->error('Import:Order:Referrer', 'Sales order referrer could not be retrieved');
		
			// Old data should be available
			if ($timestamp != 0)
			{
				return unserialize(PlentymarketsConfig::getInstance()->getMiscSalesOrderReferrerSerialized());
			}
		
			// Otherwise return an empty array
			return array();
		}
		
		// Prepare data
		$salesOrderReferrerList = array();
		foreach ($Response_GetSalesOrderReferrerList->SalesOrderReferrers->item as $SalesOrderReferrer)
		{
			$SalesOrderReferrer instanceof PlentySoapObject_GetSalesOrderReferrer;
			$salesOrderReferrerList[$SalesOrderReferrer->SalesOrderReferrerID] = array(
				'id' => $SalesOrderReferrer->SalesOrderReferrerID,
				'name' => $SalesOrderReferrer->Name
			);
		}

		PlentymarketsConfig::getInstance()->setMiscSalesOrderReferrerLastImport(time());
		PlentymarketsConfig::getInstance()->setMiscSalesOrderReferrerSerialized(serialize($salesOrderReferrerList));

		return $salesOrderReferrerList;
	}

	/**
	 *
	 * @return array
	 */
	public static function getCustomerClassList()
	{
		$timestamp = PlentymarketsConfig::getInstance()->getMiscCustomerClassLastImport(0);
		if (date('dmY') == date('dmY', $timestamp))
		{
			return unserialize(PlentymarketsConfig::getInstance()->getMiscCustomerClassSerialized());
		}

		// Do the request
		$Response_GetCustomerClassList = PlentymarketsSoapClient::getInstance()->GetCustomerClasses();
		
		// The call wasn't successful
		if (!$Response_GetCustomerClassList->Success)
		{
			// Write to log
			PlentymarketsLogger::getInstance()->error('Import:Customer:Class', 'Customer classes could not be retrieved');
			
			// Old data should be available
			if ($timestamp != 0)
			{
				return unserialize(PlentymarketsConfig::getInstance()->getMiscCustomerClassSerialized());
			}
			
			// Otherwise return an empty array
			return array();
		}

		$where = 'plentyID NOT IN (';
		
		// Prepare data
		$customerClassList = array();
		$customerClassIDs = array();
		foreach ($Response_GetCustomerClassList->CustomerClasses->item as $CustomerClass)
		{
			$CustomerClass instanceof PlentySoapObject_GetCustomerClasses;
			
			// Skip "Visible to everyone"
			if ($CustomerClass->CustomerClassID == 0)
			{
				continue;
			}
			
			$customerClassList[$CustomerClass->CustomerClassID] = array(
				'id' => (integer) $CustomerClass->CustomerClassID,
				'name' => $CustomerClass->CustomerClassName
			);
			
			$customerClassIDs[] = $CustomerClass->CustomerClassID;
		}
		
		if (!empty($customerClassIDs))
		{
			$where .= implode(',', $customerClassIDs).')';
			
			// Delete non active plentymarkets customer classes from mapping table:
			$affectedRows = Shopware()->Db()->delete('plenty_mapping_customer_class', $where);
		}

		PlentymarketsConfig::getInstance()->setMiscCustomerClassLastImport(time());
		PlentymarketsConfig::getInstance()->setMiscCustomerClassSerialized(serialize($customerClassList));

		return $customerClassList;
	}

	/**
	 *
	 * @return array
	 */
	public static function getWarehouseList()
	{
		$timestamp = PlentymarketsConfig::getInstance()->getMiscWarehousesLastImport(0);
		if (date('dmY') == date('dmY', $timestamp))
		{
			return unserialize(PlentymarketsConfig::getInstance()->getMiscWarehousesSerialized());
		}

		// Do the request
		$Response_GetWarehouseList = PlentymarketsSoapClient::getInstance()->GetWarehouseList(new PlentySoapRequest_GetWarehouseList());

		// The call wasn't successful
		if (!$Response_GetWarehouseList->Success)
		{
			// Write to log
			PlentymarketsLogger::getInstance()->error('Import:Item:Warehouse', 'Warehouses could not be retrieved');
		
			// Old data should be available
			if ($timestamp != 0)
			{
				return unserialize(PlentymarketsConfig::getInstance()->getMiscWarehousesSerialized());
			}
		
			// Otherwise return an empty array
			return array();
		}
		
		// Prepare data
		$warehouses = array(
			array(
				'id' => 0,
				'name' => 'virtuelles Gesamtlager'
			)
		);

		foreach ($Response_GetWarehouseList->WarehouseList->item as $Warehouse)
		{
			$Warehouse instanceof PlentySoapObject_GetWarehouseList;
			$warehouses[$Warehouse->WarehouseID] = array(
				'id' => (integer) $Warehouse->WarehouseID,
				'name' => $Warehouse->Name
			);
		}

		PlentymarketsConfig::getInstance()->setMiscWarehousesLastImport(time());
		PlentymarketsConfig::getInstance()->setMiscWarehousesSerialized(serialize($warehouses));

		return $warehouses;
	}

	/**
	 *
	 * @return array
	 */
	public static function getStoreList()
	{
		$timestamp = PlentymarketsConfig::getInstance()->getMiscMultishopsLastImport(0);
		if (date('dmY') == date('dmY', $timestamp))
		{
			return unserialize(PlentymarketsConfig::getInstance()->getMiscMultishopsSerialized());
		}

		// Do the request
		$Response_GetMultiShops = PlentymarketsSoapClient::getInstance()->GetMultiShops();

		// The call wasn't successful
		if (!$Response_GetMultiShops->Success)
		{
			// Write to log
			PlentymarketsLogger::getInstance()->error('Import:Store', 'Stores could not be retrieved');
		
			// Old data should be available
			if ($timestamp != 0)
			{
				return unserialize(PlentymarketsConfig::getInstance()->getMiscMultishopsSerialized());
			}
		
			// Otherwise return an empty array
			return array();
		}
		
		// Prepare data
		$multishops = array();
		foreach ($Response_GetMultiShops->MultiShops->item as $Multishop)
		{
			$Multishop instanceof PlentySoapObject_GetMultiShops;

			$name = $Multishop->MultiShopName;
			if ($Multishop->MultiShopURL)
			{
				$name .= sprintf(' (%s)', $Multishop->MultiShopURL);
			}

			$multishops[$Multishop->MultiShopsID] = array(
				'id' => $Multishop->MultiShopsID,
				'name' => $name
			);
		}

		PlentymarketsConfig::getInstance()->setMiscMultishopsLastImport(time());
		PlentymarketsConfig::getInstance()->setMiscMultishopsSerialized(serialize($multishops));

		return $multishops;
	}

	/**
	 *
	 * @return array
	 */
	public static function getShippingProfileList()
	{
		$timestamp = PlentymarketsConfig::getInstance()->getMiscShippingProfilesLastImport(0);
		if (date('dmY') == date('dmY', $timestamp))
		{
			return unserialize(PlentymarketsConfig::getInstance()->getMiscShippingProfilesSerialized());
		}

		$Request_GetShippingProfiles = new PlentySoapRequest_GetShippingProfiles();
		$Request_GetShippingProfiles->GetShippingCharges = false;
		$Request_GetShippingProfiles->ShippingProfileID = null;

		$where = 'plentyID NOT IN (';

		$providers = array();
		$shippingProfilesIDs = array();

		//
		$Response_GetShippingServiceProvider = PlentymarketsSoapClient::getInstance()->GetShippingServiceProvider();
		foreach ($Response_GetShippingServiceProvider->ShippingServiceProvider->item as $ShippingServiceProvider)
		{
			$ShippingServiceProvider instanceof PlentySoapObject_GetShippingServiceProvider;
			$providers[$ShippingServiceProvider->ShippingServiceProviderID] = $ShippingServiceProvider->ShippingServiceProviderType;
		}

		// Do the request
		$Response_GetShippingProfiles = PlentymarketsSoapClient::getInstance()->GetShippingProfiles($Request_GetShippingProfiles);

		$shippingProfiles = array();
		foreach ($Response_GetShippingProfiles->ShippingProfiles->item as $ShippingProfile)
		{
			$ShippingProfile instanceof PlentySoapObject_GetShippingProfiles;
			$shippingProfiles[$ShippingProfile->ShippingProfileID] = array(
				'id' => $ShippingProfile->ShippingProfileID,
				'name' => '[' . $providers[$ShippingProfile->ShippingServiceProviderID] . '] ' . $ShippingProfile->BackendName
			);
			
			$shippingProfilesIDs[] = $ShippingProfile->ShippingProfileID;
		}
		
		$where .= implode(',', $shippingProfilesIDs).')';
		
		// Delete non active plentymarkets shipping profiles from mapping table:
		$affectedRows = Shopware()->Db()->delete('plenty_mapping_shipping_profile', $where);

		PlentymarketsConfig::getInstance()->setMiscShippingProfilesLastImport(time());
		PlentymarketsConfig::getInstance()->setMiscShippingProfilesSerialized(serialize($shippingProfiles));

		return $shippingProfiles;
	}

	/**
	 *
	 * @return array
	 */
	public static function getVatList()
	{
		$timestamp = PlentymarketsConfig::getInstance()->getMiscVatLastImport(0);
		if (date('dmY') == date('dmY', $timestamp))
		{
			return unserialize(PlentymarketsConfig::getInstance()->getMiscVatSerialized());
		}

		$Response_GetVATConfig = PlentymarketsSoapClient::getInstance()->GetVATConfig();

		// The call wasn't successful
		if (!$Response_GetVATConfig->Success)
		{
			// Write to log
			PlentymarketsLogger::getInstance()->error('Import:VAT', 'VAT could not be retrieved');
		
			// Old data should be available
			if ($timestamp != 0)
			{
				return unserialize(PlentymarketsConfig::getInstance()->getMiscVatSerialized());
			}
		
			// Otherwise return an empty array
			return array();
		}
		
		$where = 'plentyID NOT IN (';
		
		// Prepare data
		$vat = array();
		$vatIDs = array();
		foreach ($Response_GetVATConfig->DefaultVAT->item as $VAT)
		{
			$VAT instanceof PlentySoapObject_GetVATConfig;

			if ($VAT->VATValue == 0)
			{
				continue;
			}

			$vat[$VAT->InternalVATID] = array(
				'id' => $VAT->InternalVATID,
				'name' => $VAT->VATValue . ' %'
			);
			
			$vatIDs[] = $VAT->InternalVATID;
		}
		
		$where .= implode(',', $vatIDs).')';
		
		// Delete non active plentymarkets VATs from mapping table:
		$affectedRows = Shopware()->Db()->delete('plenty_mapping_vat', $where);

		PlentymarketsConfig::getInstance()->setMiscVatLastImport(time());
		PlentymarketsConfig::getInstance()->setMiscVatSerialized(serialize($vat));

		return $vat;
	}
}
