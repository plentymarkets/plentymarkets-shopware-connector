<?php

namespace ShopwareAdapter\ServiceBus;

use DateTimeImmutable;
use PlentyConnector\Connector\ConfigService\ConfigServiceInterface;
use ReflectionClass;
use ShopwareAdapter\ShopwareAdapter;

trait ChangedDateTimeTrait
{
    /**
     * @return DateTimeImmutable
     */
    public function getChangedDateTime()
    {
        /**
         * @var ConfigServiceInterface $configService
         */
        $configService = Shopware()->Container()->get('plenty_connector.config');

        $lastRun = $configService->get($this->getKey());

        if (null === $lastRun) {
            $lastRun = '2000-01-01T00:00:00+01:00';
        }

        return DateTimeImmutable::createFromFormat(DATE_W3C, $lastRun);
    }

    /**
     * @param DateTimeImmutable $dateTime
     */
    public function setChangedDateTime(DateTimeImmutable $dateTime)
    {
        /**
         * @var ConfigServiceInterface $configService
         */
        $configService = Shopware()->Container()->get('plenty_connector.config');

        $configService->set($this->getKey(), $dateTime);
    }

    /**
     * @return DateTimeImmutable
     */
    public function getCurrentDateTime()
    {
        return new DateTimeImmutable('now');
    }

    /**
     * @return string
     */
    private function getKey()
    {
        $ref = new ReflectionClass(static::class);

        return ShopwareAdapter::NAME . '.' . $ref->getShortName() . '.LastChangeDateTime';
    }
}
