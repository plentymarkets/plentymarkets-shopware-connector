<?php

class PlentymarketsImportEntityItemPropertyGroup
{
	/**
	 *
	 * @var PlentySoapObject_PropertyGroup
	 */
	protected $Group;

	/**
	 *
	 * @param PlentySoapObject_PropertyGroup $Group
	 */
	public function __construct($Group)
	{
		$this->Group = $Group;
	}

	/**
	 *
	 */
	public function import()
	{
		try
		{
			$SHOPWARE_id = PlentymarketsMappingController::getPropertyGroupByPlentyID($this->Group->PropertyGroupID);
			PyLog()->message('Sync:Item:Producer', 'Updating the property group »' . $this->Group->FrontendName . '«');
		}
		catch (PlentymarketsMappingExceptionNotExistant $E)
		{
			PyLog()->message('Sync:Item:Producer', 'Skipping the property group »' . $this->Group->FrontendName . '«');
			return;
		}

		$Group = Shopware()->Models()->find('Shopware\Models\Property\Group', $SHOPWARE_id);
		$Group instanceof Shopware\Models\Property\Group;

		// Set the new data
		$Group->setName($this->Group->FrontendName);
		Shopware()->Models()->persist($Group);
		Shopware()->Models()->flush();
	}
}
