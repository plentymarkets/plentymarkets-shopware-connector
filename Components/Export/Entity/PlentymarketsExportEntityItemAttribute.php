<?php
require_once __DIR__ . '/../../Soap/Models/PlentySoapObject/GetItemAttributesAttribute.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapObject/GetItemAttributesAttributeValue.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapRequest/GetItemAttributes.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapObject/AddItemAttribute.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapObject/AddItemAttributeValue.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapRequest/AddItemAttribute.php';

/**
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportEntityItemAttribute
{

	/**
	 *
	 * @var array
	 */
	protected $mappingShopwareID2PlentyID = array();

	/**
	 *
	 * @var array
	 */
	protected $PLENTY_name2ID = array();

	/**
	 *
	 * @var array
	 */
	protected $PLENTY_idAndValueName2ID = array();

	/**
	 * Build the index and export the missing data to plentymarkets
	 */
	public function export()
	{
		$this->index();
		$this->doExport();
	}

	/**
	 * Build an index of the exising attributes
	 */
	protected function index()
	{
		$Request_GetItemAttributes = new PlentySoapRequest_GetItemAttributes();
		$Request_GetItemAttributes->GetValues = true; // boolean

		// Fetch the attributes form plentymarkets
		$Response_GetItemAttributes = PlentymarketsSoapClient::getInstance()->GetItemAttributes($Request_GetItemAttributes);
		foreach ($Response_GetItemAttributes->Attributes->item as $Attribute)
		{
			$this->PLENTY_name2ID[$Attribute->BackendName] = $Attribute->Id;

			$this->PLENTY_idAndValueName2ID[$Attribute->Id] = array();
			foreach ($Attribute->Values->item as $AttributeValue)
			{
				$this->PLENTY_idAndValueName2ID[$Attribute->Id][$AttributeValue->BackendName] = $AttributeValue->ValueId;
			}
		}
	}

	/**
	 * Export the missing attribtues to plentymarkets and save the mapping
	 */
	protected function doExport()
	{
		foreach (Shopware()->Models()
			->getRepository('Shopware\Models\Article\Configurator\Group')
			->findAll() as $Attribute)
		{
			$Attribute instanceof Shopware\Models\Article\Configurator\Group;

			if (array_key_exists($Attribute->getName(), $this->PLENTY_name2ID))
			{
				$attributeIdAdded = $this->PLENTY_name2ID[$Attribute->getName()];
			}

			else
			{
				$Request_AddItemAttribute = new PlentySoapRequest_AddItemAttribute();

				$Object_AddItemAttribute = new PlentySoapObject_AddItemAttribute();
				$Object_AddItemAttribute->BackendName = $Attribute->getName();
				$Object_AddItemAttribute->FrontendLang = 'de';
				$Object_AddItemAttribute->FrontendName = $Attribute->getName();
				$Object_AddItemAttribute->Position = $Attribute->getPosition();

				$Request_AddItemAttribute->Attributes[] = $Object_AddItemAttribute;
				$Response = PlentymarketsSoapClient::getInstance()->AddItemAttribute($Request_AddItemAttribute);
				$attributeIdAdded = (integer) $Response->ResponseMessages->item[0]->SuccessMessages->item[0]->Value;
			}

			PlentymarketsMappingController::addAttributeGroup($Attribute->getId(), $attributeIdAdded);

			// Values
			foreach ($Attribute->getOptions() as $AttributeValue)
			{
				$AttributeValue instanceof Shopware\Models\Article\Configurator\Option;

				if (array_key_exists($attributeIdAdded, $this->PLENTY_idAndValueName2ID) && array_key_exists($AttributeValue->getName(), $this->PLENTY_idAndValueName2ID[$attributeIdAdded]))
				{
					PlentymarketsMappingController::addAttributeOption($AttributeValue->getId(), $this->PLENTY_idAndValueName2ID[$attributeIdAdded][$AttributeValue->getName()]);
				}
				else
				{

					$Request_AddItemAttribute = new PlentySoapRequest_AddItemAttribute();

					$Object_AddItemAttribute = new PlentySoapObject_AddItemAttribute();
					$Object_AddItemAttribute->Id = $attributeIdAdded;

					$Object_AddItemAttributeValue = new PlentySoapObject_AddItemAttributeValue();
					$Object_AddItemAttributeValue->BackendName = $AttributeValue->getName();
					$Object_AddItemAttributeValue->FrontendName = $AttributeValue->getName();
					$Object_AddItemAttributeValue->Position = $AttributeValue->getPosition();

					$Object_AddItemAttribute->Values[] = $Object_AddItemAttributeValue;
					$Request_AddItemAttribute->Attributes[] = $Object_AddItemAttribute;
					$Response = PlentymarketsSoapClient::getInstance()->AddItemAttribute($Request_AddItemAttribute);

					foreach ($Response->ResponseMessages->item[0]->SuccessMessages->item as $MessageItem)
					{
						if ($MessageItem->Key == 'AttributeValueID')
						{
							PlentymarketsMappingController::addAttributeOption($AttributeValue->getId(), $MessageItem->Value);
						}
					}
				}
			}
		}
	}
}
