<?php
require_once __DIR__ . '/../Soap/Models/PlentySoapObject/Integer.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapObject/ItemAttributeMarkup.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapObject/ItemAttributeValueSet.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapObject/ItemAvailability.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapObject/ItemBase.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapObject/ItemCategory.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapObject/ItemFreeTextFields.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapObject/ItemOthers.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapObject/ItemPriceSet.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapObject/ItemProperty.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapObject/ItemStock.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapObject/ItemSupplier.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapObject/ItemTexts.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapObject/String.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapRequest/GetItemsBase.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapObject/ItemPriceSet.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapRequestObject/GetItemsPriceLists.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapRequest/GetItemsPriceLists.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapObject/GetCurrentStocks.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapRequestObject/GetCurrentStocks.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapRequest/GetCurrentStocks.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapObject/DeliveryCountryVAT.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapObject/GetVATConfig.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapObject/GetWarehouseList.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapRequest/GetWarehouseList.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapObject/GetMethodOfPayments.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapObject/Integer.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapRequest/GetMethodOfPayments.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapObject/GetShippingProfiles.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapObject/ShippingCharges.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapObject/ShippingChargesCosts.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapRequest/GetShippingProfiles.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapObject/GetMultiShops.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapRequest/GetOrderStatusList.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapRequest/SearchOrders.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapRequest/GetItemsPriceUpdate.php';
require_once __DIR__ . '/../Soap/Models/PlentySoapResponseObject/GetItemsPriceUpdate.php';

require_once __DIR__ . '/Entity/PlentymarketsImportEntityItem.php';
require_once __DIR__ . '/Entity/PlentymarketsImportEntityItemLinked.php';
require_once __DIR__ . '/Entity/PlentymarketsImportEntityItemStock.php';

class PlentymarketsImportController
{

	/**
	 * Reads the items of plentymarkets that have changed
	 */
	public static function getItemsBase()
	{
		$Request_GetItemsBase = new PlentySoapRequest_GetItemsBase();
		$Request_GetItemsBase->GetAttributeValueSets = true; // boolean
		$Request_GetItemsBase->GetCategories = true; // boolean
		$Request_GetItemsBase->GetCategoryNames = true; // boolean
		$Request_GetItemsBase->GetItemAttributeMarkup = true; // boolean
		$Request_GetItemsBase->GetItemOthers = true; // boolean
		$Request_GetItemsBase->GetItemProperties = true; // boolean
		$Request_GetItemsBase->GetItemSuppliers = false; // boolean
		$Request_GetItemsBase->GetItemURL = 0; // int
		$Request_GetItemsBase->GetLongDescription = true; // boolean
		$Request_GetItemsBase->GetMetaDescription = false; // boolean
		$Request_GetItemsBase->GetShortDescription = true; // boolean
		$Request_GetItemsBase->GetTechnicalData = false; // boolean
		$Request_GetItemsBase->WebstoreID = PlentymarketsConfig::getInstance()->getWebstoreID(0);
		$Request_GetItemsBase->Lang = 'de'; // string
		$Request_GetItemsBase->LastUpdateFrom = PlentymarketsConfig::getInstance()->getImportItemLastUpdateTimestamp(0);
		$Request_GetItemsBase->Page = 0;

		PlentymarketsLogger::getInstance()->message('Sync:Item', 'LastUpdate: ' . date('r', PlentymarketsConfig::getInstance()->getImportItemLastUpdateTimestamp(0)));
		PlentymarketsLogger::getInstance()->message('Sync:Item', 'WebstoreID: ' . $Request_GetItemsBase->WebstoreID);
		$lastUpdateTimestamp = time();

		$numberOfItemsUpdated = 0;
		$itemIds = array();

		do
		{

			// Do the request
			$Response_GetItemsBase = PlentymarketsSoapClient::getInstance()->GetItemsBase($Request_GetItemsBase);

			$pages = max($Response_GetItemsBase->Pages, 1);

			PlentymarketsLogger::getInstance()->message('Sync:Item', 'Page: ' . ($Request_GetItemsBase->Page + 1) . '/' . $pages);

			if ($Response_GetItemsBase->Success == false)
			{
				PlentymarketsLogger::getInstance()->error('Sync:Item', 'failed');
				break;
			}

			PlentymarketsLogger::getInstance()->message('Sync:Item', 'Received ' . count($Response_GetItemsBase->ItemsBase->item) . ' items');

			foreach ($Response_GetItemsBase->ItemsBase->item as $ItemBase)
			{
				try
				{
					// If this is the first run, ignore all SWAG items
					if ($Request_GetItemsBase->LastUpdateFrom == 0 && preg_match('!^Swag/\d+$!', $ItemBase->ExternalItemID))
					{
						PlentymarketsLogger::getInstance()->message('Sync:Item', 'Skipping previously exportet item with the plentymarkets item id ' . $ItemBase->ItemID);
						continue;
					}

					$Importuer = new PlentymarketsImportEntityItem($ItemBase);
					$Importuer->import();

					$itemIds[] = $ItemBase->ItemID;

					++$numberOfItemsUpdated;
				}
				catch (Exception $E)
				{
					PlentymarketsLogger::getInstance()->error('Sync:Item', 'Item with the plentymarkets item id ' . $ItemBase->ItemID . ' could not be importet');
					PlentymarketsLogger::getInstance()->error('Sync:Item', $E->getMessage());
				}
			}
		}

		// Until all pages are received
		while (++$Request_GetItemsBase->Page < $Response_GetItemsBase->Pages);

		// Crosselling
		foreach ($itemIds as $itemId)
		{
			try
			{
				// Crosselling
				$ItemLinker = new PlentymarketsImportEntityItemLinked($itemId);
				$ItemLinker->link();
			}
			catch (Exception $E)
			{
				PlentymarketsLogger::getInstance()->error('Sync:Item:Linked', 'Item linker for the item with the plentymarkets item id ' . $ItemBase->ItemID . ' failed' . $E->getMessage());
				PlentymarketsLogger::getInstance()->error('Sync:Item:Linked', $E->getMessage());
			}
		}

		PlentymarketsLogger::getInstance()->message('Sync:Item', $numberOfItemsUpdated . ' items have been updated');
		PlentymarketsConfig::getInstance()->setImportItemLastUpdateTimestamp($lastUpdateTimestamp);
	}

	/**
	 * Updates the item prices
	 */
	public static function importItemPrices()
	{
		// Dependencies
		$numberOfPricesUpdates = 0;

		// Warenbestände abrufen (für ein bestimmtes Lager, oder -1)

		PlentymarketsLogger::getInstance()->message('Sync:Item:Price', 'LastUpdate: ' . date('r', PlentymarketsConfig::getInstance()->getImportItemPriceLastUpdateTimestamp(time())));
		$timestamp = PlentymarketsConfig::getInstance()->getImportItemPriceLastUpdateTimestamp(time());
		$now = time();

		$Request_GetItemsPriceUpdate = new PlentySoapRequest_GetItemsPriceUpdate();
		$Request_GetItemsPriceUpdate->LastUpdateFrom = $timestamp; // int
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
						$sku = explode($ItemsPriceUpdate->SKU);
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
		$Request_SearchOrders = new PlentySoapRequest_SearchOrders();
		$Request_SearchOrders->GetIncomingPayments = false; // boolean
		$Request_SearchOrders->GetOrderCustomerAddress = false; // boolean
		$Request_SearchOrders->GetOrderDeliveryAddress = false; // boolean
		$Request_SearchOrders->GetOrderDocumentNumbers = false; // boolean
		$Request_SearchOrders->GetOrderInfo = false; // boolean
		$Request_SearchOrders->GetParcelService = false; // boolean
		$Request_SearchOrders->GetSalesOrderProperties = false; // boolean
		$Request_SearchOrders->WebstoreID = PlentymarketsConfig::getInstance()->getWebstoreID(); // int
		$Request_SearchOrders->OrderType = 'order'; // string
		$Request_SearchOrders->Page = 0;

		$timestamp = PlentymarketsConfig::getInstance()->getImportOrderLastUpdateTimestamp(0);
		PlentymarketsLogger::getInstance()->message('Sync:Order:OutgoingItems', 'LastUpdate: ' . date('r', $timestamp));

		if (PlentymarketsConfig::getInstance()->getOutgoingItemsOrderStatus(0))
		{
			$Request_SearchOrders->LastUpdateFrom = $timestamp; // int
			$Request_SearchOrders->OrderStatus = (float) PlentymarketsConfig::getInstance()->getOutgoingItemsOrderStatus(); // float
			PlentymarketsLogger::getInstance()->message('Sync:Order:OutgoingItems', 'Mode: Status (' . $Request_SearchOrders->OrderStatus . ')');
		}

		else
		{
			$Request_SearchOrders->OrderCompletedFrom = $timestamp; // int
			PlentymarketsLogger::getInstance()->message('Sync:Order:OutgoingItems', 'Mode: Outgoing items');
		}

		// Helper
		$timestamp = time();
		$numberOfOrdersUpdated = 0;
		$OrderResource = \Shopware\Components\Api\Manager::getResource('Order');
		$orderModule = Shopware()->Modules()->Order();
		$orderStatus = PlentymarketsConfig::getInstance()->getOutgoingItemsShopwareOrderStatusID(7);

		do
		{
			$Response_SearchOrders = PlentymarketsSoapClient::getInstance()->SearchOrders($Request_SearchOrders);

			//
			$pages = max($Response_SearchOrders->Pages, 1);

			PlentymarketsLogger::getInstance()->message('Sync:Order:OutgoingItems', 'Page: ' . ($Request_SearchOrders->Page + 1) . '/' . $pages);

			if ($Response_SearchOrders->Success == false)
			{
				PlentymarketsLogger::getInstance()->error('Sync:Order:OutgoingItems', 'failed');
				break;
			}

			PlentymarketsLogger::getInstance()->message('Sync:Order:OutgoingItems', 'Received ' . count($Response_SearchOrders->Orders->item) . ' items');

			// Jeden Artikel in Shopware "importieren"
			foreach ($Response_SearchOrders->Orders->item as $Order)
			{
				$Order = $Order->OrderHead;
				$Order instanceof PlentySoapObject_OrderHead;
				try
				{
					$orderId = $Order->ExternalOrderID;
					if (strstr($orderId, 'Swag/') === false)
					{
						PlentymarketsLogger::getInstance()->error('Sync:Order:OutgoingItems', 'The sales order with the external order id ' . $Order->ExternalOrderID . ' could not be updated because it isn\'t a shopware order');
						continue;
					}

					$SHOPWARE_orderId = PlentymarketsUtils::getShopwareIDFromExternalOrderID($orderId);
					if ($SHOPWARE_orderId <= 0)
					{
						PlentymarketsLogger::getInstance()->error('Sync:Order:OutgoingItems', 'The sales order with the external order id ' . $Order->ExternalOrderID . ' could not be updated');
						continue;
					}

					$orderModule->setOrderStatus($SHOPWARE_orderId, $orderStatus, false, 'plentymarkets');
					PlentymarketsLogger::getInstance()->message('Sync:Order:OutgoingItems', 'The sales order with the id ' . $orderId . ' has been marked sent.');

					++$numberOfOrdersUpdated;
				}
				catch (Exception $E)
				{
					PlentymarketsLogger::getInstance()->error('Sync:Order:OutgoingItems', 'The sales order with the external order id ' . $Order->ExternalOrderID . ' could not be updated');
					PlentymarketsLogger::getInstance()->error('Sync:Order:OutgoingItems', $E->getMessage());
				}
			}
		}

		// Until all pages are received
		while (++$Request_SearchOrders->Page < $Response_SearchOrders->Pages);

		//
		PlentymarketsLogger::getInstance()->message('Sync:Order:OutgoingItems', $numberOfOrdersUpdated . ' sales orders have been marked as sent');
		PlentymarketsConfig::getInstance()->setImportOrderLastUpdateTimestamp($timestamp);
	}

	/**
	 * Update stocks
	 */
	public static function importItemStocks()
	{
		$Request_GetCurrentStocks = new PlentySoapRequest_GetCurrentStocks();
		$Request_GetCurrentStocks->LastUpdate = PlentymarketsConfig::getInstance()->getImportItemStockLastUpdateTimestamp(-1); // int
		$Request_GetCurrentStocks->Page = 0;
		$Request_GetCurrentStocks->WarehouseID = PlentymarketsConfig::getInstance()->getItemWarehouseID(0); // int

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
				PlentymarketsImportEntityItemStock::update($itemDetailsID, $CurrentStock->NetStock);
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
	 * @return array
	 */
	public static function getMethodsOfPayment()
	{
		$timestamp = PlentymarketsConfig::getInstance()->getMiscMethodsOfPaymentLastImport(0);
		if (date("dmY") == date('dmY', $timestamp))
		{
			return unserialize(PlentymarketsConfig::getInstance()->getMiscMethodsOfPaymentSerialized());
		}

		$Request_GetMethodOfPayments = new PlentySoapRequest_GetMethodOfPayments();
		$Request_GetMethodOfPayments->ActivMethodOfPayments = false;

		// Do the request
		$Response_GetMethodOfPayments = PlentymarketsSoapClient::getInstance()->GetMethodOfPayments($Request_GetMethodOfPayments);

		$methodOfPayments = array();
		foreach ($Response_GetMethodOfPayments->MethodOfPayment->item as $MethodOfPayment)
		{
			$MethodOfPayment instanceof PlentySoapObject_GetMethodOfPayments;
			$methodOfPayments[$MethodOfPayment->MethodOfPaymentID] = array(
				'id' => $MethodOfPayment->MethodOfPaymentID,
				'name' => $MethodOfPayment->Name
			);
		}

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
		if (date("dmY") == date('dmY', $timestamp))
		{
			return unserialize(PlentymarketsConfig::getInstance()->getMiscOrderStatusSerialized());
		}

		$Request_GetOrderStatusList = new PlentySoapRequest_GetOrderStatusList();
		$Request_GetOrderStatusList->Lang = 'de';

		// Do the request
		$Response_GetOrderStatusList = PlentymarketsSoapClient::getInstance()->GetOrderStatusList($Request_GetOrderStatusList);

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
		if (date("dmY") == date('dmY', $timestamp))
		{
			return unserialize(PlentymarketsConfig::getInstance()->getMiscSalesOrderReferrerSerialized());
		}

		// Do the request
		$Response_GetSalesOrderReferrerList = PlentymarketsSoapClient::getInstance()->GetSalesOrderReferrer();

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
	public static function getWarehouses()
	{
		$timestamp = PlentymarketsConfig::getInstance()->getMiscWarehousesLastImport(0);
		if (date("dmY") == date('dmY', $timestamp))
		{
			return unserialize(PlentymarketsConfig::getInstance()->getMiscWarehousesSerialized());
		}

		// Do the request
		$Response_GetWarehouseList = PlentymarketsSoapClient::getInstance()->GetWarehouseList(new PlentySoapRequest_GetWarehouseList());

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
				'id' => $Warehouse->WarehouseID,
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
	public static function getMultishops()
	{
		$timestamp = PlentymarketsConfig::getInstance()->getMiscMultishopsLastImport(0);
		if (date("dmY") == date('dmY', $timestamp))
		{
			return unserialize(PlentymarketsConfig::getInstance()->getMiscMultishopsSerialized());
		}

		// Do the request
		$Response_GetMultiShops = PlentymarketsSoapClient::getInstance()->GetMultiShops();

		$multishops = array();
		foreach ($Response_GetMultiShops->MultiShops->item as $Multishop)
		{
			$Multishop instanceof PlentySoapObject_GetMultiShops;
			$multishops[$Multishop->MultiShopsID] = array(
				'id' => $Multishop->MultiShopsID,
				'name' => $Multishop->MultiShopName . ' (' . $Multishop->MultiShopURL . ')'
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
	public static function getShippingProfiles()
	{
		$timestamp = PlentymarketsConfig::getInstance()->getMiscShippingProfilesLastImport(0);
		if (date("dmY") == date('dmY', $timestamp))
		{
			return unserialize(PlentymarketsConfig::getInstance()->getMiscShippingProfilesSerialized());
		}

		$Request_GetShippingProfiles = new PlentySoapRequest_GetShippingProfiles();
		$Request_GetShippingProfiles->GetShippingCharges = false; // boolean
		$Request_GetShippingProfiles->ShippingProfileID = null; // int

		$providers = array();

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
		}

		PlentymarketsConfig::getInstance()->setMiscShippingProfilesLastImport(time());
		PlentymarketsConfig::getInstance()->setMiscShippingProfilesSerialized(serialize($shippingProfiles));

		return $shippingProfiles;
	}

	/**
	 *
	 * @return array
	 */
	public static function getVat()
	{
		$timestamp = PlentymarketsConfig::getInstance()->getMiscVatLastImport(0);
		if (date("dmY") == date('dmY', $timestamp))
		{
			return unserialize(PlentymarketsConfig::getInstance()->getMiscVatSerialized());
		}

		$Response_GetVATConfig = PlentymarketsSoapClient::getInstance()->GetVATConfig();

		$vat = array();
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
		}

		PlentymarketsConfig::getInstance()->setMiscVatLastImport(time());
		PlentymarketsConfig::getInstance()->setMiscVatSerialized(serialize($vat));

		return $vat;
	}
}
