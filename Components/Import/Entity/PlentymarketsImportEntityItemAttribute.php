<?php

class PlentymarketsImportEntityItemAttribute
{
	/**
	 *
	 * @var PlentySoapObject_GetItemAttributesAttribute
	 */
	protected $Attribute;

	/**
	 *
	 * @var Shopware\Models\Article\Configurator\Group
	 */
	protected $Group;

	/**
	 *
	 * @param PlentySoapObject_GetItemAttributesAttribute $Attribute
	 */
	public function __construct($Attribute)
	{
		$this->Attribute = $Attribute;
	}

	public function __destruct()
	{
		if ($this->Group)
		{
			Shopware()->Models()->persist($this->Group);
			Shopware()->Models()->flush();
		}
	}

	/**
	 *
	 */
	public function import()
	{
		$this->importAttribute();
		$this->importValues();
	}

	protected function importAttribute()
	{
		try
		{
			$SHOPWARE_attributeId = PlentymarketsMappingController::getAttributeGroupByPlentyID($this->Attribute->Id);
			PyLog()->message('Sync:Item:Attribute', 'Updating the attribute »' . $this->Attribute->FrontendName . '«');
		}
		catch (PlentymarketsMappingExceptionNotExistant $E)
		{
			PyLog()->message('Sync:Item:Attribute', 'Skipping the attribute »' . $this->Attribute->FrontendName . '«');
			return;
		}

		$Group = Shopware()->Models()->find('Shopware\Models\Article\Configurator\Group', $SHOPWARE_attributeId);
		$Group instanceof Shopware\Models\Article\Configurator\Group;

		// Set the new data
		$Group->setName($this->Attribute->FrontendName);
		$Group->setPosition($this->Attribute->Position);

		$this->Group = $Group;
	}

	protected function importValues()
	{
		if (!$this->Group)
		{
			return;
		}

		foreach ($this->Attribute->Values->item as $Value)
		{
			$Value instanceof PlentySoapObject_GetItemAttributesAttributeValue;

			try
			{
				$SHOPWARE_optionId = PlentymarketsMappingController::getAttributeOptionByPlentyID($Value->ValueId);
			}
			catch (PlentymarketsMappingExceptionNotExistant $E)
			{
				return;
			}

			foreach ($this->Group->getOptions() as $Option)
			{
				$Option instanceof Shopware\Models\Article\Configurator\Option;
				if ($Option->getId() != $SHOPWARE_optionId)
				{
					continue;
				}

				$Option->setName($Value->FrontendName);
				$Option->setPosition($Value->Position);
			}
		}
	}
}
