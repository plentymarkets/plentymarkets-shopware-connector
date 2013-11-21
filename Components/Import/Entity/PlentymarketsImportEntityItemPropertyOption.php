<?php

class PlentymarketsImportEntityItemPropertyOption
{

	/**
	 *
	 * @var PlentySoapObject_Property
	 */
	protected $Option;

	/**
	 *
	 * @param PlentySoapObject_Property $Group
	 */
	public function __construct($Option)
	{
		$this->Option = $Option;
	}

	/**
	 */
	public function import()
	{
		try
		{
			$SHOPWARE_id = PlentymarketsMappingController::getPropertyByPlentyID($this->Option->PropertyID);
			PyLog()->message('Sync:Item:Property:Option', 'Updating the property option »' . $this->Option->PropertyFrontendName . '«');
		}
		catch (PlentymarketsMappingExceptionNotExistant $E)
		{
			PyLog()->message('Sync:Item:Property:Option', 'Skipping the property option »' . $this->Option->PropertyFrontendName . '«');
			return;
		}

		list ($groupId, $optionId) = explode(';', $SHOPWARE_id);

		$Option = Shopware()->Models()->find('Shopware\Models\Property\Option', $optionId);
		$Option instanceof Shopware\Models\Property\Option;

		// Set the new data
		$Option->setName($this->Option->PropertyFrontendName);
		Shopware()->Models()->persist($Option);
		Shopware()->Models()->flush();
	}
}
