<?php

namespace ShopwareAdapter\ServiceBus;

use DateTimeImmutable;
use ReflectionClass;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\ConfigService\ConfigServiceInterface;

trait ChangedDateTimeTrait
{
    public function getChangedDateTime(): DateTimeImmutable
    {
        /**
         * @var ConfigServiceInterface $configService
         */
        $configService = Shopware()->Container()->get('plenty_connector.config_service');

        $lastRun = $configService->get($this->getKey());

        if (null === $lastRun) {
            $lastRun = '2000-01-01T00:00:00+01:00';
        }

        return DateTimeImmutable::createFromFormat(DATE_W3C, $lastRun);
    }

    public function setChangedDateTime(DateTimeImmutable $dateTime)
    {
        /**
         * @var ConfigServiceInterface $configService
         */
        $configService = Shopware()->Container()->get('plenty_connector.config_service');

        $configService->set($this->getKey(), $dateTime);
    }

    public function getCurrentDateTime(): DateTimeImmutable
    {
        return new DateTimeImmutable('now');
    }

    private function getKey(): string
    {
        $ref = new ReflectionClass(static::class);

        return ShopwareAdapter::NAME . '.' . $ref->getShortName() . '.LastChangeDateTime';
    }
}
