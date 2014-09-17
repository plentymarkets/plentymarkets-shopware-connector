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
 * PlentymarketsExportControllerItemProperty provides the actual items export functionality. Like the other export
 * entities this class is called in PlentymarketsExportController.
 * The data export takes place based on plentymarkets SOAP-calls.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportControllerItemProperty
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
	public function run()
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
			throw new PlentymarketsExportException('The item properties could not be retrieved', 2940);
		}

		foreach ($Response_GetPropertiesList->PropertyGroups->item as $PropertyGroup)
		{
			$this->PLENTY_groupName2ID[$PropertyGroup->PropertyGroupName] = $PropertyGroup->PropertyGroupID;
			$this->PLENTY_groupIDValueName2ID[$PropertyGroup->PropertyGroupID] = array();

			/** @var PlentySoapObject_GetPropertiesListPropertie $PropertyValue */
			foreach ($PropertyGroup->Properties->item as $PropertyValue)
			{
				// Sales parameter
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
		$propertyGroupRepository = Shopware()->Models()->getRepository('Shopware\Models\Property\Group');

		/** @var Shopware\Models\Property\Group $PropertyGroup */
		foreach ($propertyGroupRepository->findAll() as $PropertyGroup)
		{
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
					$Request_SetPropertyGroups = new PlentySoapRequest_SetPropertyGroups();

					$Request_SetPropertyGroups->Properties = array();

					$Object_SetPropertyGroup = new PlentySoapObject_SetPropertyGroup();
					$Object_SetPropertyGroup->BackendName = $PropertyGroup->getName();
					$Object_SetPropertyGroup->FrontendName = $PropertyGroup->getName();
					$Object_SetPropertyGroup->Lang = 'de';
					$Object_SetPropertyGroup->PropertyGroupID = null;

					$Request_SetPropertyGroups->PropertyGroups[] = $Object_SetPropertyGroup;

					$Response = PlentymarketsSoapClient::getInstance()->SetPropertyGroups($Request_SetPropertyGroups);

					if (!$Response->Success)
					{
						throw new PlentymarketsExportException('The item property group »'. $Object_SetPropertyGroup->BackendName .'« could not be exported', 2941);
					}

					$groupIdAdded = (integer) $Response->ResponseMessages->item[0]->SuccessMessages->item[0]->Value;
				}

				PlentymarketsMappingController::addPropertyGroup($PropertyGroup->getId(), $groupIdAdded);
			}

			if (!isset($this->PLENTY_groupIDValueName2ID[$groupIdAdded]))
			{
				$this->PLENTY_groupIDValueName2ID[$groupIdAdded] = array();
			}

			$Request_SetProperties = new PlentySoapRequest_SetProperties();
			$Request_SetProperties->Properties = array();
			
			$Object_SetProperty = new PlentySoapObject_SetProperty();
			$Object_SetProperty->PropertyGroupID = $groupIdAdded;
			$Object_SetProperty->PropertyID = 0;
			$Object_SetProperty->Lang = 'de';

			/** @var Shopware\Models\Property\Option $Property */
			foreach ($PropertyGroup->getOptions() as $Property)
			{
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
						$Object_SetProperty->PropertyFrontendName = $Property->getName();
						$Object_SetProperty->PropertyBackendName = $Property->getName();
						$Object_SetProperty->ShowOnItemPage = 1;
						$Object_SetProperty->ShowInItemList = 1;
						$Object_SetProperty->PropertyType = 'text';

						$Request_SetProperties->Properties[] = $Object_SetProperty;

						$Response_SetProperties = PlentymarketsSoapClient::getInstance()->SetProperties($Request_SetProperties);

						if (!$Response_SetProperties->Success)
						{
							throw new PlentymarketsExportException('The item property »'. $Object_SetProperty->PropertyBackendName .'« could not be created', 2942);
						}

						$propertyIdAdded = (integer) $Response_SetProperties->ResponseMessages->item[0]->SuccessMessages->item[0]->Value;
					}

					PlentymarketsMappingController::addProperty($shopwareID, $propertyIdAdded);
				}
			}
		}
	}

	/**
	 * Checks whether the export is finshed
	 *
	 * @return boolean
	 */
	public function isFinished()
	{
		return true;
	}
}
