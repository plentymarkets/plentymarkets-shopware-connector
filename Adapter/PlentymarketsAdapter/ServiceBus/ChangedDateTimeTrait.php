<?php

namespace PlentymarketsAdapter\ServiceBus;

use DateTimeImmutable;
use DateTimeZone;
use PlentyConnector\Connector\ConfigService\ConfigServiceInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;

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
         * @var ConfigServiceInterface
         */
        $config = Shopware()->Container()->get('plenty_connector.config');

        $timezone = new DateTimeZone('UTC');
        $lastRun = $config->get($this->getKey(), '2000-01-01');

        return new DateTimeImmutable($lastRun, $timezone);
    }

    /**
     * @param DateTimeImmutable $dateTime
     */
    public function setChangedDateTime(DateTimeImmutable $dateTime)
    {
        /**
         * @var ConfigServiceInterface
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
        return PlentymarketsAdapter::NAME.get_called_class().'.LastChangeDateTime';
    }
}
