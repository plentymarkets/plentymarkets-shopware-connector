<?php
require_once __DIR__ . '/../../Soap/Models/PlentySoapObject/GetPropertiesListGroup.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapObject/GetPropertiesListPropertie.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapRequest/GetPropertiesList.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapRequest/AddPropertyGroup.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapRequest/AddProperty.php';

/**
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
	 *
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
	 * Export the missin properties
	 */
	protected function doExport()
	{
		$mappingPropertyGroup = array();
		$mappingPropertyValue = array();

		$propertyGroupRepository = Shopware()->Models()->getRepository('Shopware\Models\Property\Group');
		foreach ($propertyGroupRepository->findAll() as $PropertyGroup)
		{
			$PropertyGroup instanceof Shopware\Models\Property\Group;

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
				$groupIdAdded = (integer) $Response->ResponseMessages->item[0]->SuccessMessages->item[0]->Value;
			}

			PlentymarketsMappingController::addPropertyGroup($PropertyGroup->getId(), $groupIdAdded);
			$this->PLENTY_groupIDValueName2ID[$groupIdAdded] = array();

			$Request_AddProperty = new PlentySoapRequest_AddProperty();
			$Request_AddProperty->PropertyGroupID = $groupIdAdded;
			$Request_AddProperty->PropertyID = 0;
			$Request_AddProperty->Lang = 'de';

			foreach ($PropertyGroup->getOptions() as $Property)
			{
				$Property instanceof Shopware\Models\Property\Option;

				// In plenty exisitert in dieser Gruppe bereits dieser Wert
				if (array_key_exists($Property->getName(), $this->PLENTY_groupIDValueName2ID[$groupIdAdded]))
				{
					$propertyIdAdded = $this->PLENTY_groupIDValueName2ID[$groupIdAdded][$Property->getName()];
				}
				else
				{
					$Request_AddProperty->PropertyFrontendName = $Property->getName();
					$Request_AddProperty->PropertyBackendName = $Property->getName();
					$Request_AddProperty->PropertyType = 'text';

					$Response_AddProperty = PlentymarketsSoapClient::getInstance()->AddProperty($Request_AddProperty);

					$propertyIdAdded = (integer) $Response_AddProperty->ResponseMessages->item[0]->SuccessMessages->item[0]->Value;
				}

				PlentymarketsMappingController::addProperty($PropertyGroup->getId() . ';' . $Property->getId(), $propertyIdAdded);
			}
		}
	}
}
