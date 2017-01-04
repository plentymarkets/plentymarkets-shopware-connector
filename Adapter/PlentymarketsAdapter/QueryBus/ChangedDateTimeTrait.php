<?php

namespace PlentymarketsAdapter\QueryBus;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use PlentyConnector\Connector\ConfigService\ConfigServiceInterface;

/**
 * Class ChangedDateTimeTrait.
 */
trait ChangedDateTimeTrait
{
    /**
     * @param ConfigServiceInterface $config
     *
     * @return string
     */
    public function getChangedDateTime(ConfigServiceInterface $config)
    {
        $key = 'PlentymarketsAdapter.' . get_called_class() . 'DateTime';

        $timezone = new DateTimeZone('UTC');
        $lastRun = $config->get($key, '2000-01-01');

        $dateTime = new DateTimeImmutable($lastRun, $timezone);

        return $dateTime->format(DateTime::ATOM);
    }

    /**
     * @param ConfigServiceInterface $config
     */
    public function setChangedDateTime(ConfigServiceInterface $config)
    {
        $key = 'PlentymarketsAdapter.' . get_called_class() . 'DateTime';

        $timezone = new DateTimeZone('UTC');
        $dateTime = new DateTimeImmutable('now', $timezone);

        $config->set($key, $dateTime);
    }
}
