<?php

namespace PlentyConnector\Components\Sepa\Shopware;

use Assert\Assertion;
use PlentyConnector\Components\Sepa\PaymentData\SepaPaymentData;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Currency\Currency;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentyConnector\Connector\TransferObject\Payment\Payment;
use PlentyConnector\Connector\TransferObject\PaymentMethod\PaymentMethod;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Shop\Currency as CurrencyModel;
use ShopwareAdapter\ResponseParser\Payment\PaymentResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class SepaPaymentResponseParser
 */
class SepaPaymentResponseParser implements PaymentResponseParserInterface
{
    /**
     * @var PaymentResponseParserInterface
     */
    private $parentResponseParser;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * SepaPaymentResponseParser constructor.
     *
     * @param PaymentResponseParserInterface $parentResponseParser
     * @param IdentityServiceInterface       $identityService
     */
    public function __construct(
        PaymentResponseParserInterface $parentResponseParser,
        IdentityServiceInterface $identityService
    ) {
        $this->parentResponseParser = $parentResponseParser;
        $this->identityService = $identityService;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $element)
    {
        $payments = $this->parentResponseParser->parse($element);

        if (!$this->hasSepaPaymentInstance($element)) {
            return $payments;
        }

        if (empty($payments)) {
            $identifier = $this->getIdentifier($element['id'], Payment::TYPE);

            $payment = new Payment();
            $payment->setOrderIdentifer($this->getIdentifier($element['id'], Order::TYPE));
            $payment->setIdentifier($identifier);
            $payment->setTransactionReference($identifier);
            $payment->setCurrencyIdentifier($this->getIdentifier($this->getCurrencyId($element['currency']), Currency::TYPE));
            $payment->setPaymentMethodIdentifier($this->getIdentifier($element['paymentId'], PaymentMethod::TYPE));

            $payments = [$payment];
        }

        foreach ($payments as $payment) {
            if (!($payment instanceof Payment)) {
                continue;
            }

            $payment->setPaymentData($this->getSepaPaymentData($element));
        }

        return $payments;
    }

    /**
     * @param int    $entry
     * @param string $type
     *
     * @return string
     */
    private function getIdentifier($entry, $type)
    {
        Assertion::integerish($entry);

        return $this->identityService->findOneOrThrow(
            (string) $entry,
            ShopwareAdapter::NAME,
            $type
        )->getObjectIdentifier();
    }

    /**
     * @param string $currency
     *
     * @return int
     */
    private function getCurrencyId($currency)
    {
        /**
         * @var ModelRepository $currencyRepository
         */
        $currencyRepository = Shopware()->Models()->getRepository(CurrencyModel::class);

        return $currencyRepository->findOneBy(['currency' => $currency])->getId();
    }

    /**
     * @param array $element
     *
     * @return SepaPaymentData
     */
    private function getSepaPaymentData(array $element)
    {
        $paymentInstance = array_shift($element['paymentInstances']);

        $sepaPaymentData = new SepaPaymentData();
        $sepaPaymentData->setAccountOwner($paymentInstance['accountHolder']);
        $sepaPaymentData->setIban($paymentInstance['iban']);
        $sepaPaymentData->setBic($paymentInstance['bic']);

        return $sepaPaymentData;
    }

    /**
     * @param array $element
     *
     * @return bool
     */
    private function hasSepaPaymentInstance(array $element)
    {
        if (empty($element['paymentInstances'])) {
            return false;
        }

        $paymentInstance = array_shift($element['paymentInstances']);

        if (empty($paymentInstance['accountHolder'])) {
            return false;
        }

        if (empty($paymentInstance['iban'])) {
            return false;
        }

        if (empty($paymentInstance['bic'])) {
            return false;
        }

        return true;
    }
}
