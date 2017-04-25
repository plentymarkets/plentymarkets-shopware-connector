<?php
/**
 * plentymarkets shopware connector
 * Copyright © 2013 plentymarkets GmbH
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 * The texts of the GNU Affero General Public License, supplemented by an additional
 * permission, and of our proprietary license can be found
 * in the LICENSE file you have received along with this program.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 * "plentymarkets" is a registered trademark of plentymarkets GmbH.
 * "shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, titles and interests in the
 * above trademarks remain entirely with the trademark owners.
 *
 * @copyright Copyright (c) 2013, plentymarkets GmbH (http://www.plentymarkets.com)
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
use Shopware\Models\Order\Order;

/**
 * PlentymarketsImportEntityOrderPackageNumber
 */
class PlentymarketsImportEntityOrderPackageNumber extends PlentymarketsImportEntityOrderAbstract
{
    /**
     * @var string
     */
    protected static $action = 'ImportPackageNumber';

    /**
     * Prepares the soap orders SOAP object
     *
     * @see PlentymarketsImportEntityOrderPackageNumber::prepare()
     */
    public function prepare()
    {
        $timestamp = (int) PlentymarketsConfig::getInstance()->getImportOrderPackageNumberLastUpdateTimestamp(0);

        if (!$timestamp) {
            $timestamp = time();
        }

        $this->log('LastUpdate: ' . date('r', $timestamp));
        $this->Request_SearchOrders->LastUpdateFrom = $timestamp;
    }

    /**
     * Handles the actual import
     *
     * @param int $shopwareOrderId
     * @param PlentySoapObject_OrderHead $Order
     */
    public function handle($shopwareOrderId, $Order)
    {
        try {
            /**
             * @var \Shopware\Models\Order\Repository $orderRepository
             * @var Order $order
             */
            $orderRepository = Shopware()->Models()->getRepository(Order::class);
            $order =  $orderRepository->find($shopwareOrderId);
            $order->setTrackingCode($Order->PackageNumber);

            Shopware()->Models()->persist($order);
            Shopware()->Models()->flush();

        } catch (Exception $e) {
            $logger = PlentymarketsLogger::getInstance();
            $logger->error(self::$action, $e->getMessage());
        }

    }
}
