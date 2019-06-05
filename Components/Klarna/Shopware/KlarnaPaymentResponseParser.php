<?php

namespace PlentyConnector\Components\Klarna\Shopware;

use Doctrine\DBAL\Connection;
use Exception;
use PlentyConnector\Components\Klarna\PaymentData\KlarnaPaymentData;
use Shopware\Components\Plugin\CachedConfigReader;
use ShopwareAdapter\ResponseParser\Payment\PaymentResponseParserInterface;
use SystemConnector\TransferObject\Payment\Payment;

class KlarnaPaymentResponseParser implements PaymentResponseParserInterface
{
    /**
     * ISO3166_AT
     *
     * @var int
     */
    const AT = 15;

    /**
     * ISO3166_DK
     *
     * @var int
     */
    const DK = 59;

    /**
     * ISO3166_FI
     *
     * @var int
     */
    const FI = 73;

    /**
     * ISO3166_DE
     *
     * @var int
     */
    const DE = 81;

    /**
     * ISO3166_NL
     *
     * @var int
     */
    const NL = 154;

    /**
     * ISO3166_NO
     *
     * @var int
     */
    const NO = 164;

    /**
     * ISO3166_SE
     *
     * @var int
     */
    const SE = 209;

    /**
     * @var PaymentResponseParserInterface
     */
    private $parentResponseParser;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var CachedConfigReader
     */
    private $configReader;

    public function __construct(
        PaymentResponseParserInterface $parentResponseParser,
        Connection $connection,
        CachedConfigReader $configReader
    ) {
        $this->parentResponseParser = $parentResponseParser;
        $this->connection = $connection;
        $this->configReader = $configReader;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $element): array
    {
        $payments = $this->parentResponseParser->parse($element);

        foreach ($payments as $payment) {
            if (!($payment instanceof Payment)) {
                continue;
            }

            $this->addKlarnaPaymentData($payment, $element);
        }

        return $payments;
    }

    /**
     * @param Payment $payment
     * @param array   $element
     */
    private function addKlarnaPaymentData(Payment $payment, array $element)
    {
        if ('payment_klarna_kpm' !== $element['payment']['action']) {
            return;
        }

        $klarnaConfig = $this->configReader->getByPluginName('SwagPaymentKlarnaKpm');
        $klarnaShopId = $klarnaConfig['merchantId'];

        $paymentData = new KlarnaPaymentData();
        $paymentData->setPclassId('-1');

        if ('klarna_account' === $element['payment']['name']) {
            $paymentData->setPclassId($this->getKlarnaPclassId($klarnaShopId, $element['billing']['country']['iso']));
        }

        $paymentData->setShopId($klarnaShopId);
        $paymentData->setTransactionId($element['transactionId']);

        $payment->setPaymentData($paymentData);
    }

    /**
     * @param string $klarnaShopId
     * @param string $countryIso
     *
     * @return string
     */
    private function getKlarnaPclassId($klarnaShopId, $countryIso)
    {
        try {
            $query = 'SELECT id FROM s_klarna_pclasses 
                      WHERE eid = :klarnaShopId 
                      AND country = :country';

            return $this->connection->fetchColumn($query, [
                'klarnaShopId' => $klarnaShopId,
                'country' => $this->getKlarnaCountryId($countryIso), ]
            );
        } catch (Exception $exception) {
            return '';
        }
    }

    /**
     * @param string $country
     *
     * @return int
     */
    private function getKlarnaCountryId($country)
    {
        switch ($country) {
            case 'DE':
                return self::DE;
            case 'AT':
                return self::AT;
            case 'DK':
                return self::DK;
            case 'FI':
                return self::FI;
            case 'NL':
                return self::NL;
            case 'NO':
                return self::NO;
            case 'SE':
                return self::SE;
            default:
                return 0;
        }
    }
}
