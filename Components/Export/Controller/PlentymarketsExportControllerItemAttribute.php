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

require_once PY_SOAP . 'Models/PlentySoapObject/GetItemAttributesAttribute.php';
require_once PY_SOAP . 'Models/PlentySoapObject/GetItemAttributesAttributeValue.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/GetItemAttributes.php';
require_once PY_SOAP . 'Models/PlentySoapObject/AddItemAttribute.php';
require_once PY_SOAP . 'Models/PlentySoapObject/AddItemAttributeValue.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/AddItemAttribute.php';
require_once PY_COMPONENTS . 'Export/PlentymarketsExportException.php';

/**
 * PlentymarketsExportControllerItemAttribute provides the actual items export functionality. Like the other export
 * entities this class is called in PlentymarketsExportController.
 * The data export takes place based on plentymarkets SOAP-calls.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportControllerItemAttribute
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
	 * Build an index of the exising attributes
	 */
	protected function index()
	{
		$Request_GetItemAttributes = new PlentySoapRequest_GetItemAttributes();
		$Request_GetItemAttributes->GetValues = true; // boolean
		$Request_GetItemAttributes->Lang = 'de';

		// Fetch the attributes form plentymarkets
		$Response_GetItemAttributes = PlentymarketsSoapClient::getInstance()->GetItemAttributes($Request_GetItemAttributes);

		if (!$Response_GetItemAttributes->Success)
		{
			throw new PlentymarketsExportException('The item attributes could not be retrieved', 2910);
		}

		foreach ($Response_GetItemAttributes->Attributes->item as $Attribute)
		{
			$this->PLENTY_name2ID[strtolower($Attribute->BackendName)] = $Attribute->Id;

			$this->PLENTY_idAndValueName2ID[$Attribute->Id] = array();
			foreach ($Attribute->Values->item as $AttributeValue)
			{
				$this->PLENTY_idAndValueName2ID[$Attribute->Id][strtolower($AttributeValue->BackendName)] = $AttributeValue->ValueId;
			}
		}
	}

	/**
	 * Build the index and export the missing data to plentymarkets
	 */
	public function run()
	{
		$this->index();
		$this->doExport();
	}

	/**
	 * Export the missing attribtues to plentymarkets and save the mapping
	 */
	protected function doExport()
	{
		// Repository
		$Repository = Shopware()->Models()->getRepository('Shopware\Models\Article\Configurator\Group');

		// Chunk configuration
		$chunk = 0;
		$size = PlentymarketsConfig::getInstance()->getInitialExportChunkSize(PlentymarketsExportController::DEFAULT_CHUNK_SIZE);

		do {

			PlentymarketsLogger::getInstance()->message('Export:Initial:Attribute', 'Chunk: '. ($chunk + 1));
			$Groups = $Repository->findBy(array(), null, $size, $chunk * $size);

			foreach ($Groups as $Attribute)
			{
				$Attribute instanceof Shopware\Models\Article\Configurator\Group;

				$Request_AddItemAttribute = new PlentySoapRequest_AddItemAttribute();

				$Object_AddItemAttribute = new PlentySoapObject_AddItemAttribute();
				$Object_AddItemAttribute->BackendName = sprintf('%s (Sw %d)', $Attribute->getName(), $Attribute->getId());
				$Object_AddItemAttribute->FrontendLang = 'de';
				$Object_AddItemAttribute->FrontendName = $Attribute->getName();
				$Object_AddItemAttribute->Position = $Attribute->getPosition();

				try
				{
					$attributeIdAdded = PlentymarketsMappingController::getAttributeGroupByShopwareID($Attribute->getId());
				}
				catch (PlentymarketsMappingExceptionNotExistant $E)
				{
					if (isset($this->PLENTY_name2ID[strtolower($Object_AddItemAttribute->BackendName)]))
					{
						$attributeIdAdded = $this->PLENTY_name2ID[strtolower($Object_AddItemAttribute->BackendName)];
					}
					else
					{
						$Request_AddItemAttribute->Attributes[] = $Object_AddItemAttribute;
						$Response = PlentymarketsSoapClient::getInstance()->AddItemAttribute($Request_AddItemAttribute);

						if (!$Response->Success)
						{
							throw new PlentymarketsExportException('The item attribute »'. $Object_AddItemAttribute->BackendName .'« could not be created', 2911);
						}

						$attributeIdAdded = (integer) $Response->ResponseMessages->item[0]->SuccessMessages->item[0]->Value;
					}

					// Add the mapping
					PlentymarketsMappingController::addAttributeGroup($Attribute->getId(), $attributeIdAdded);
				}

				// Values
				foreach ($Attribute->getOptions() as $AttributeValue)
				{
					$AttributeValue instanceof Shopware\Models\Article\Configurator\Option;

					$Request_AddItemAttribute = new PlentySoapRequest_AddItemAttribute();

					$Object_AddItemAttribute = new PlentySoapObject_AddItemAttribute();
					$Object_AddItemAttribute->Id = $attributeIdAdded;

					$Object_AddItemAttributeValue = new PlentySoapObject_AddItemAttributeValue();
					$Object_AddItemAttributeValue->BackendName = sprintf('%s (Sw %d)', $AttributeValue->getName(), $AttributeValue->getId());
					$Object_AddItemAttributeValue->FrontendName = $AttributeValue->getName();
					$Object_AddItemAttributeValue->Position = $AttributeValue->getPosition();

					$Object_AddItemAttribute->Values[] = $Object_AddItemAttributeValue;
					$Request_AddItemAttribute->Attributes[] = $Object_AddItemAttribute;

					try
					{
						PlentymarketsMappingController::getAttributeOptionByShopwareID($AttributeValue->getId());
					}
					catch (PlentymarketsMappingExceptionNotExistant $E)
					{
						// Workaround
						$checknameValue = strtolower(str_replace(',', '.', $Object_AddItemAttributeValue->BackendName));

						if (isset($this->PLENTY_idAndValueName2ID[$attributeIdAdded][$checknameValue]))
						{
							PlentymarketsMappingController::addAttributeOption($AttributeValue->getId(), $this->PLENTY_idAndValueName2ID[$attributeIdAdded][$checknameValue]);
						}
						else
						{
							$Response = PlentymarketsSoapClient::getInstance()->AddItemAttribute($Request_AddItemAttribute);

							if (!$Response->Success)
							{
								throw new PlentymarketsExportException('The item attribute option »'. $Object_AddItemAttributeValue->BackendName .'« could not be created', 2912);
							}

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

			++$chunk;

		} while (!empty($Groups) && count($Groups) == $size);
	}

	/**
	 * Checks whether the export is finished
	 *
	 * @return boolean
	 */
	public function isFinished()
	{
		return true;
	}
}
