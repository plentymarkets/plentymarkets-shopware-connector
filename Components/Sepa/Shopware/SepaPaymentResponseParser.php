<?php

namespace PlentyConnector\Components\Sepa\Shopware;

use Assert\Assertion;
use Doctrine\ORM\EntityRepository;
use InvalidArgumentException;
use PlentyConnector\Components\Sepa\PaymentData\SepaPaymentData;
use Shopware\Models\Shop\Currency as CurrencyModel;
use ShopwareAdapter\ResponseParser\Payment\PaymentResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\Currency\Currency;
use SystemConnector\TransferObject\Order\Order;
use SystemConnector\TransferObject\Payment\Payment;
use SystemConnector\TransferObject\PaymentMethod\PaymentMethod;
use SystemConnector\TransferObject\Shop\Shop;

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
    public function parse(array $element): array
    {
        $payments = $this->parentResponseParser->parse($element);

        if (!$this->hasSepaPaymentInstance($element)) {
            return $payments;
        }

        if (empty($payments)) {
            $identifier = $this->getIdentifier($element['id'], Payment::TYPE);

            $payment = new Payment();
            $payment->setOrderIdentifier($this->getIdentifier($element['id'], Order::TYPE));
            $payment->setIdentifier($identifier);
            $payment->setShopIdentifier($this->getIdentifier($element['shopId'], Shop::TYPE));
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
     */
    private function getIdentifier($entry, $type): string
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
     */
    private function getCurrencyId($currency): int
    {
        /**
         * @var EntityRepository $currencyRepository
         */
        $currencyRepository = Shopware()->Models()->getRepository(CurrencyModel::class);

        /**
         * @var null|CurrencyModel $model
         */
        $model = $currencyRepository->findOneBy(['currency' => $currency]);

        if (null === $model) {
            throw new InvalidArgumentException('could not find currency model by currency: ' . $currency);
        }

        return $model->getId();
    }

    private function getSepaPaymentData(array $element): SepaPaymentData
    {
        $paymentInstance = array_shift($element['paymentInstances']);

        $sepaPaymentData = new SepaPaymentData();
        $sepaPaymentData->setAccountOwner($paymentInstance['accountHolder']);
        $sepaPaymentData->setIban($paymentInstance['iban']);
        $sepaPaymentData->setBic($paymentInstance['bic']);

        return $sepaPaymentData;
    }

    private function hasSepaPaymentInstance(array $element): bool
    {
        if (empty($element['paymentInstances'])) {
            return false;
        }

        $paymentInstance = array_shift($element['paymentInstances']);

        if (empty($paymentInstance['iban'])) {
            return false;
        }

        if (empty($paymentInstance['bic'])) {
            return false;
        }

        return true;
    }
}
