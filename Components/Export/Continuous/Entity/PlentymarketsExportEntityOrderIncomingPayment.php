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
 * PlentymarketsExportEntityOrderIncomingPayment provides the actual incoming payments export functionality. Like the other export
 * entities this class is called in PlentymarketsExportController. It is important to deliver a valid order ID
 * to the constructor method of this class.
 * The data export takes place based on plentymarkets SOAP-calls.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportEntityOrderIncomingPayment
{
    /**
     * Shopware order data
     *
     * @var array
     */
    protected $order = array();

    /**
     * plentymarkets order data (out of shopware)
     *
     * @var array
     */
    protected $plentyOrder = array();

    /**
     * @var Klarna
     */
    protected $klarnaService;

    /**
     * Constructor method
     *
     * @param integer $orderID Shopware order id
     * @throws Exception
     */
    public function __construct($orderID)
    {
        $OrderResource = Shopware\Components\Api\Manager::getResource('Order');

        try {
            $this->order = $OrderResource->getOne($orderID);
        } catch (\Shopware\Components\Api\Exception\NotFoundException $E) {
            throw new PlentymarketsExportEntityException('The incoming payment of the order with the id »' . $orderID . '« could not be booked (order not found)', 4110);
        }

        $Result = Shopware()->Db()->query('SELECT * FROM plenty_order WHERE shopwareId = ?', array(
            $orderID
        ));

        $plentyOrder = $Result->fetchObject();

        if (!is_object($plentyOrder) || (integer)$plentyOrder->plentyOrderId <= 0) {
            throw new PlentymarketsExportEntityException('The incoming payment of the order with the number »' . $this->order['number'] . '« could not be booked (order was not yet exported)', 4120);
        }

        if (!is_null($plentyOrder->plentyOrderPaidTimestamp)) {
            throw new PlentymarketsExportEntityException('The incoming payment of the order with the number »' . $this->order['number'] . '« could not be booked (has already been exported)', 4130);
        }

        $this->plentyOrder = $plentyOrder;

        /**
         * @var Klarna $klarna
         */
        $this->klarnaService = Shopware()->Container()->get('KlarnaService');
    }

    /**
     * Books the incoming payment
     */
    public function book()
    {
        $methodOfPaymentId = PlentymarketsMappingController::getMethodOfPaymentByShopwareID($this->order['paymentId']);

        if ($methodOfPaymentId == MOP_AMAZON_PAYMENT) {
            PlentymarketsLogger::getInstance()->message('Sync:Order:IncomingPayment', 'The incoming payment of the order with the number »' . $this->order['number'] . '« was ignored (Amazon Payment)');

            return;
        }

        $transactionId = '';

        if ($methodOfPaymentId == MOP_KLARNA || $methodOfPaymentId == MOP_KLARNACREDIT) {
            $transactionId = $this->getKlarnaTransactionId();
            $reasonForPayment = '';
        } else {
            $reasonForPayment = sprintf('Shopware (OrderId: %u, CustomerId: %u)', $this->order['id'], $this->order['customerId']);
        }

        $Request_AddIncomingPayments = new PlentySoapRequest_AddIncomingPayments();
        $Request_AddIncomingPayments->IncomingPayments = array();

        $Object_AddIncomingPayments = new PlentySoapObject_AddIncomingPayments();
        $Object_AddIncomingPayments->Amount = $this->order['invoiceAmount'];
        $Object_AddIncomingPayments->Currency = PlentymarketsMappingController::getCurrencyByShopwareID($this->order['currency']);
        $Object_AddIncomingPayments->CustomerEmail = $this->order['customer']['email'];
        $Object_AddIncomingPayments->CustomerID = $this->getCustomerId();
        $Object_AddIncomingPayments->CustomerName = $this->getCustomerName();
        $Object_AddIncomingPayments->MethodOfPaymentID = $methodOfPaymentId;
        $Object_AddIncomingPayments->OrderID = $this->plentyOrder->plentyOrderId;
        $Object_AddIncomingPayments->ReasonForPayment = $reasonForPayment;

        if ($transactionId) {
            $Object_AddIncomingPayments->TransactionID = $transactionId;
        } else if (empty($this->order['transactionId'])) {
            $Object_AddIncomingPayments->TransactionID = $Object_AddIncomingPayments->ReasonForPayment;
        } else {
            $Object_AddIncomingPayments->TransactionID = $this->order['transactionId'];
        }

        if ($this->object['clearedDate'] instanceof DateTime) {
            $Object_AddIncomingPayments->TransactionTime = $this->order['clearedDate']->getTimestamp();
        } else {
            $Object_AddIncomingPayments->TransactionTime = time();
        }

        $Request_AddIncomingPayments->IncomingPayments[] = $Object_AddIncomingPayments;
        $Response_AddIncomingPayments = PlentymarketsSoapClient::getInstance()->AddIncomingPayments($Request_AddIncomingPayments);

        // Check for success
        if ($Response_AddIncomingPayments->Success) {
            PlentymarketsLogger::getInstance()->message('Sync:Order:IncomingPayment', 'The incoming payment of the order with the number »' . $this->order['number'] . '« was booked');
            Shopware()->Db()->query('
					UPDATE plenty_order
						SET
							plentyOrderPaidStatus = 1,
							plentyOrderPaidTimestamp = NOW()
						WHERE shopwareId = ?
				', array(
                $this->order['id']
            ));
        } else {
            throw new PlentymarketsExportEntityException('The incoming payment of the order with the number »' . $this->order['number'] . '« could not be booked', 4140);
        }
    }

    /**
     * Returns the plentymarkets customer id
     *
     * @throws PlentymarketsExportEntityException
     * @return integer
     */
    protected function getCustomerId()
    {
        try {
            return PlentymarketsMappingController::getCustomerByShopwareID($this->order['billing']['id']);
        } catch (PlentymarketsMappingExceptionNotExistant $E) {
            // Customer needs to be re-exported
            PlentymarketsLogger::getInstance()->message('Sync:Order:IncomingPayment', 'Re-exporting customer');
        }

        // Get the data
        $Customer = Shopware()->Models()->find('Shopware\Models\Customer\Customer', $this->order['customerId']);
        $BillingAddress = Shopware()->Models()->find('Shopware\Models\Order\Billing', $this->order['billing']['id']);

        try // Export
        {
            $PlentymarketsExportEntityCustomer = new PlentymarketsExportEntityCustomer($Customer, $BillingAddress);
            $PlentymarketsExportEntityCustomer->export();
        } catch (PlentymarketsExportEntityException $E) {
            throw new PlentymarketsExportEntityException('The incoming payment of the order with the number »' . $this->order['number'] . '« could not be booked (' . $E->getMessage() . ')', 4150);
        }

        return PlentymarketsMappingController::getCustomerByShopwareID($this->order['billing']['id']);
    }

    /**
     * Returns the full customer name
     *
     * @return string
     */
    protected function getCustomerName()
    {
        return sprintf('%s %s', $this->order['billing']['firstName'], $this->order['billing']['lastName']);
    }

    /**
     * checks of a plugin is installed and active
     *
     * @param $name
     * @return bool
     */
    protected function pluginExists($name)
    {
        $sql = 'SELECT 1 FROM s_core_plugins WHERE name = ? AND active = 1';
        $test = Shopware()->Db()->fetchOne($sql, array($name));

        if (empty($test)) {
            return false;
        }

        return true;
    }

    /**
     * @return int|null
     */
    private function getKlarnaPClass()
    {
        $pClasses = $this->klarnaService->getPClasses();

        if (isset($pClasses[0])) {
            return $pClasses[0];
        }

        return null;
    }

    /**
     * Returns the klarna transaction id
     *
     * @return string
     */
    protected function getKlarnaTransactionId()
    {
        $orderNumber = $this->order['number'];

        try {
            if ($this->pluginExists('PigmbhKlarnaPayment')) {
                // eid / shop_id
                $sql = 'SELECT shop_id FROM Pi_klarna_payment_multistore WHERE order_number = ?';
                $multistore = Shopware()->Db()->query($sql, array($orderNumber))->fetchObject();

                // pclass
                $sql = 'SELECT pclassid FROM Pi_klarna_payment_pclass where ordernumber = ?';
                $pclass = Shopware()->Db()->query($sql, array($orderNumber))->fetchObject();

                // Transaction ID
                $sql = 'SELECT transactionid FROM Pi_klarna_payment_order_data WHERE order_number = ?';
                $order = Shopware()->Db()->query($sql, array($orderNumber))->fetchObject();

                return sprintf('%s_%s_%s', $order->transactionid, $pclass->pclassid, $multistore->shop_id);
            } elseif ($this->pluginExists('SwagPaymentKlarna') || $this->pluginExists('SwagPaymentKlarnaKpm')) {
                if (!empty($this->order['languageIso'])) {
                    $this->klarnaService->setLanguage($this->order['languageIso']);
                } else {
                    throw new Exception('order language missing');
                }

                if (!empty($this->order['billing']['country']['iso'])) {
                    $this->klarnaService->setCountry($this->order['billing']['country']['iso']);
                } else {
                    throw new Exception('order country missing');
                }

                if (!empty($this->order['currency'])) {
                    $this->klarnaService->setCurrency($this->order['currency']);
                } else {
                    throw new Exception('order currency missing');
                }

                /**
                 * @var KlarnaPClass $pclass
                 */
                $pclass = $this->getKlarnaPClass();

                if (!empty($pclass)) {
                    if (isset($this->order['payment']['name']) && $this->order['payment']['name'] == 'klarna_invoice') {
                        $classId = KlarnaPClass::INVOICE;
                    } else {
                        $classId = $pclass->getId();
                    }

                    return sprintf('%s_%s_%s', $this->order['transactionId'], $classId, $pclass->getEid());
                } else {
                    throw new Exception('No compatible pclass found');
                }
            } else {
                throw new Exception('No compatible Klarna payment plugin found');
            }
        } catch (Exception $e) {
            PyLog()->error('Sync:Order:PaymentKlarna', $e->getMessage());
        }

        return '';
    }
}
