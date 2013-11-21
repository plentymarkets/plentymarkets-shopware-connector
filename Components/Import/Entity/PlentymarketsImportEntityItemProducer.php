<?php

class PlentymarketsImportEntityItemProducer
{
	/**
	 *
	 * @var PlentySoapObject_GetProducers
	 */
	protected $Producer;

	/**
	 *
	 * @param PlentySoapObject_GetProducers $Producer
	 */
	public function __construct(PlentySoapObject_GetProducers $Producer)
	{
		$this->Producer = $Producer;
	}

	/**
	 *
	 */
	public function import()
	{
		try
		{
			$SHOPWARE_id = PlentymarketsMappingController::getProducerByPlentyID($this->Producer->ProducerID);
			PyLog()->message('Sync:Item:Producer', 'Updating the producer »' . $this->Producer->ProducerName . '«');
		}
		catch (PlentymarketsMappingExceptionNotExistant $E)
		{
			PyLog()->message('Sync:Item:Producer', 'Skipping the producer »' . $this->Producer->ProducerName . '«');
			return;
		}

		$Supplier = Shopware()->Models()->find('Shopware\Models\Article\Supplier', $SHOPWARE_id);
		$Supplier instanceof Shopware\Models\Article\Supplier;

		// Set the new data
		$Supplier->setName($this->Producer->ProducerName);
		Shopware()->Models()->persist($Supplier);
		Shopware()->Models()->flush();
	}
}
