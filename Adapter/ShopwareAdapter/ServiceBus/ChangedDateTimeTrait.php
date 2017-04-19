<?php

namespace ShopwareAdapter\ServiceBus;

use DateTimeImmutable;
use DateTimeZone;
use PlentyConnector\Connector\ConfigService\ConfigServiceInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class ChangedDateTimeTrait.
 */
trait ChangedDateTimeTrait
{
    /**
     * @return DateTimeImmutable
     */
    public function getChangedDateTime()
    {
        /**
         * @var ConfigServiceInterface $config
         */
        $config = Shopware()->Container()->get('plenty_connector.config');

        $timezone = new DateTimeZone('UTC');
        $lastRun = $config->get($this->getKey());

        if (null === $lastRun) {
            $lastRun = '2000-01-01T00:00:00+01:00';
        }

        return DateTimeImmutable::createFromFormat(DATE_W3C, $lastRun, $timezone);
    }

    /**
     * @param DateTimeImmutable $dateTime
     */
    public function setChangedDateTime(DateTimeImmutable $dateTime)
    {
        /**
         * @var ConfigServiceInterface $config
         */
        $config = Shopware()->Container()->get('plenty_connector.config');

        $config->set($this->getKey(), $dateTime);
    }

    /**
     * @return DateTimeImmutable
     */
    public function getCurrentDateTime()
    {
        $timezone = new DateTimeZone('UTC');

        return new DateTimeImmutable('now', $timezone);
    }

    /**
     * @return string
     */
    private function getKey()
    {
        return ShopwareAdapter::NAME . get_called_class() . '.LastChangeDateTime';
    }
}
