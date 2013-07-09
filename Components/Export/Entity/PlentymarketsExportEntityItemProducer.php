<?php
require_once __DIR__ . '/../../Soap/Models/PlentySoapObject/Producer.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapRequest/SetProducers.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapObject/GetProducers.php';

/**
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportEntityItemProducer
{

	/**
	 *
	 * @var array
	 */
	protected $PLENTY_name2ID = array();

	/**
	 * Build the index of existing data
	 */
	protected function buildPlentyNameIndex()
	{
		$Response_GetProducers = PlentymarketsSoapClient::getInstance()->GetProducers();

		foreach ($Response_GetProducers->Producers->item as $Producer)
		{
			$this->PLENTY_name2ID[$Producer->ProducerName] = $Producer->ProducerID;
		}
	}

	/**
	 * Build the index and start the export
	 */
	public function export()
	{
		// Index first
		$this->buildPlentyNameIndex();
		$this->doExport();
	}

	/**
	 * Export the missind producers
	 */
	protected function doExport()
	{

		$producerNameMappingShopware = array();
		$supplierRepository = Shopware()->Models()->getRepository('Shopware\Models\Article\Supplier');

		$Request_SetProducers = new PlentySoapRequest_SetProducers();
		foreach ($supplierRepository->findAll() as $Supplier)
		{
			$Supplier instanceof Shopware\Models\Article\Supplier;
			$Object_SetProducer = new PlentySoapObject_Producer();

			if (array_key_exists($Supplier->getName(), $this->PLENTY_name2ID))
			{
				PlentymarketsMappingController::addProducer($Supplier->getId(), $this->PLENTY_name2ID[$Supplier->getName()]);
			}
			else
			{
				$Object_SetProducer->ProducerExternalName = $Supplier->getName();
				$Object_SetProducer->ProducerName = $Supplier->getName();
				$Object_SetProducer->ProducerHomepage = $Supplier->getLink();
				$Request_SetProducers->Producers[] = $Object_SetProducer;
				$producerNameMappingShopware[$Supplier->getName()] = $Supplier->getId();
			}
		}

		if (count($Request_SetProducers->Producers))
		{
			$Response_SetProducers = PlentymarketsSoapClient::getInstance()->SetProducers($Request_SetProducers);
			foreach ($Response_SetProducers->ResponseMessages->item as $ResponseMessage)
			{
				PlentymarketsMappingController::addProducer(
					$producerNameMappingShopware[$ResponseMessage->IdentificationValue],
					$ResponseMessage->SuccessMessages->item[0]->Value
				);
			}
		}
	}
}
