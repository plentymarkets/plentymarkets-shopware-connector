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
 * PlentymarketsExportControllerItemProducer provides the actual items export functionality. Like the other export
 * entities this class is called in PlentymarketsExportController.
 * The data export takes place based on plentymarkets SOAP-calls.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportControllerItemProducer
{
    /**
     * @var array
     */
    protected $PLENTY_name2ID = [];

    /**
     * Build the index and start the export
     */
    public function run()
    {
        // Index first
        $this->buildPlentyNameIndex();
        $this->doExport();
    }

    /**
     * Checks whether the export is finshed
     *
     * @return bool
     */
    public function isFinished()
    {
        return true;
    }

    /**
     * Build the index of existing data
     */
    protected function buildPlentyNameIndex()
    {
        $Response_GetProducers = PlentymarketsSoapClient::getInstance()->GetProducers();

        if (!$Response_GetProducers->Success) {
            throw new PlentymarketsExportException('The item producers could not be retrieved', 2930);
        }

        foreach ($Response_GetProducers->Producers->item as $Producer) {
            $this->PLENTY_name2ID[$Producer->ProducerName] = $Producer->ProducerID;
        }
    }

    /**
     * Export the missing producers
     */
    protected function doExport()
    {
        $producers = [];
        $producerNameMappingShopware = [];
        $supplierRepository = Shopware()->Models()->getRepository('Shopware\Models\Article\Supplier');

        $Request_SetProducers = new PlentySoapRequest_SetProducers();

        /** @var Shopware\Models\Article\Supplier $Supplier */
        foreach ($supplierRepository->findAll() as $Supplier) {
            $Object_SetProducer = new PlentySoapObject_Producer();

            if (array_key_exists($Supplier->getName(), $this->PLENTY_name2ID)) {
                PlentymarketsMappingController::addProducer($Supplier->getId(), $this->PLENTY_name2ID[$Supplier->getName()]);
            } else {
                // Request object
                $Object_SetProducer->ProducerExternalName = $Supplier->getName();
                $Object_SetProducer->ProducerName = $Supplier->getName();
                $Object_SetProducer->ProducerHomepage = $Supplier->getLink();

                // Export-array
                $producers[] = $Object_SetProducer;

                // Save name and id for the mapping
                $producerNameMappingShopware[$Supplier->getName()] = $Supplier->getId();
            }
        }

        // Chunkify since the call can only handly 50 producers at a time
        $chunks = array_chunk($producers, 50);

        foreach ($chunks as $chunk) {
            // Set the request
            $Request_SetProducers->Producers = $chunk;

            // Do the call
            $Response_SetProducers = PlentymarketsSoapClient::getInstance()->SetProducers($Request_SetProducers);

            if (!$Response_SetProducers->Success) {
                throw new PlentymarketsExportException('The item producers could not be created', 2931);
            }

            // Create mapping
            foreach ($Response_SetProducers->ResponseMessages->item as $ResponseMessage) {
                PlentymarketsMappingController::addProducer(
                    $producerNameMappingShopware[$ResponseMessage->IdentificationValue],
                    $ResponseMessage->SuccessMessages->item[0]->Value
                );
            }
        }
    }
}
