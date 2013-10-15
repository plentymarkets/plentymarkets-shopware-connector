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

require_once PY_SOAP . 'Models/PlentySoapObject/GetPropertiesListGroup.php';
require_once PY_SOAP . 'Models/PlentySoapObject/GetPropertiesListPropertie.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/GetPropertiesList.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/AddPropertyGroup.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/AddProperty.php';

/**
 * PlentymarketsExportEntityItemProperty provides the actual items export funcionality. Like the other export
 * entities this class is called in PlentymarketsExportController.
 * The data export takes place based on plentymarkets SOAP-calls.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportEntityItemProperty
{

	/**
	 *
	 * @var array
	 */
	protected $PLENTY_groupName2ID = array();

	/**
	 *
	 * @var array
	 */
	protected $PLENTY_groupIDValueName2ID = array();

	/**
	 * Created an index of the plentymarkets data and exports the missing
	 */
	public function export()
	{
		$this->buildPlentyIndex();
		$this->doExport();
	}

	/**
	 * Build an index of the existing data
	 * @todo language
	 */
	protected function buildPlentyIndex()
	{
		$Request_GetPropertiesList = new PlentySoapRequest_GetPropertiesList();
		$Request_GetPropertiesList->Lang = 'de'; // string

		$Response_GetPropertiesList = PlentymarketsSoapClient::getInstance()->GetPropertiesList($Request_GetPropertiesList);

		if (!$Response_GetPropertiesList->Success)
		{
			throw new \Exception('Cannot fetch PropertiesList');
		}

		foreach ($Response_GetPropertiesList->PropertyGroups->item as $PropertyGroup)
		{
			$this->PLENTY_groupName2ID[$PropertyGroup->PropertyGroupName] = $PropertyGroup->PropertyGroupID;
			$this->PLENTY_groupIDValueName2ID[$PropertyGroup->PropertyGroupID] = array();
			foreach ($PropertyGroup->Properties->item as $PropertyValue)
			{
				$PropertyValue instanceof PlentySoapObject_GetPropertiesListPropertie;

				// Bestellmerkmal
				if ($PropertyValue->IsSalesOrderParam)
				{
					continue;
				}

				// Not a text property
				if ($PropertyValue->PropertyValueType != 'text')
				{
					continue;
				}

				$this->PLENTY_groupIDValueName2ID[$PropertyGroup->PropertyGroupID][$PropertyValue->PropertyName] = $PropertyValue->PropertyID;
			}
		}
	}

	/**
	 * Export the missing properties
	 */
	protected function doExport()
	{
		$mappingPropertyGroup = array();
		$mappingPropertyValue = array();

		$propertyGroupRepository = Shopware()->Models()->getRepository('Shopware\Models\Property\Group');
		foreach ($propertyGroupRepository->findAll() as $PropertyGroup)
		{
			$PropertyGroup instanceof Shopware\Models\Property\Group;
			try
			{
				$groupIdAdded = PlentymarketsMappingController::getPropertyGroupByShopwareID($PropertyGroup->getId());
			}
			catch (PlentymarketsMappingExceptionNotExistant $E)
			{
				if (array_key_exists($PropertyGroup->getName(), $this->PLENTY_groupName2ID))
				{
					$groupIdAdded = $this->PLENTY_groupName2ID[$PropertyGroup->getName()];
				}
				else
				{
					$Request_AddPropertyGroup = new PlentySoapRequest_AddPropertyGroup();
					$Request_AddPropertyGroup->BackendName = $PropertyGroup->getName();
					$Request_AddPropertyGroup->FrontendName = $PropertyGroup->getName();
					$Request_AddPropertyGroup->Lang = 'de';
					$Request_AddPropertyGroup->PropertyGroupID = 0;

					$Response = PlentymarketsSoapClient::getInstance()->AddPropertyGroup($Request_AddPropertyGroup);

					if (!$Response->Success)
					{
						throw new \Exception('Cannot export property group "'. $Request_AddPropertyGroup->BackendName .'"');
					}

					$groupIdAdded = (integer) $Response->ResponseMessages->item[0]->SuccessMessages->item[0]->Value;
				}

				PlentymarketsMappingController::addPropertyGroup($PropertyGroup->getId(), $groupIdAdded);
			}

			if (!isset($this->PLENTY_groupIDValueName2ID[$groupIdAdded]))
			{
				$this->PLENTY_groupIDValueName2ID[$groupIdAdded] = array();
			}

			$Request_AddProperty = new PlentySoapRequest_AddProperty();
			$Request_AddProperty->PropertyGroupID = $groupIdAdded;
			$Request_AddProperty->PropertyID = 0;
			$Request_AddProperty->Lang = 'de';

			foreach ($PropertyGroup->getOptions() as $Property)
			{
				$Property instanceof Shopware\Models\Property\Option;

				$shopwareID = $PropertyGroup->getId() . ';' . $Property->getId();

				try
				{
					PlentymarketsMappingController::getPropertyByShopwareID($shopwareID);
				}
				catch (PlentymarketsMappingExceptionNotExistant $E)
				{
					if (array_key_exists($Property->getName(), $this->PLENTY_groupIDValueName2ID[$groupIdAdded]))
					{
						$propertyIdAdded = $this->PLENTY_groupIDValueName2ID[$groupIdAdded][$Property->getName()];
					}
					else
					{
						$Request_AddProperty->PropertyFrontendName = $Property->getName();
						$Request_AddProperty->PropertyBackendName = $Property->getName();
						$Request_AddProperty->ShowOnItemPage = 1;
						$Request_AddProperty->ShowInItemList = 1;
						$Request_AddProperty->PropertyType = 'text';

						$Response_AddProperty = PlentymarketsSoapClient::getInstance()->AddProperty($Request_AddProperty);

						if (!$Response_AddProperty->Success)
						{
							throw new \Exception('Cannot export property "'. $Request_AddProperty->PropertyBackendName .'"');
						}

						$propertyIdAdded = (integer) $Response_AddProperty->ResponseMessages->item[0]->SuccessMessages->item[0]->Value;
					}

					PlentymarketsMappingController::addProperty($shopwareID, $propertyIdAdded);
				}
			}
		}
	}
}
