<?php

namespace PlentymarketsAdapter\ServiceBus;

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
        $key = 'PlentymarketsAdapter.' . $this->getClassName(get_called_class()) . '.LastChangeDateTime';

        $timezone = new DateTimeZone('UTC');
        $lastRun = $config->get($key, '2000-01-01');

        $dateTime = new DateTimeImmutable($lastRun, $timezone);

        return $dateTime->format(DateTime::ATOM);
    }

    /**
     * @param $class
     *
     * @return string
     */
    private function getClassName($class)
    {
        return substr(strrchr(get_class($class), '\\'), 1);
    }

    /**
     * @param ConfigServiceInterface $config
     */
    public function setChangedDateTime(ConfigServiceInterface $config)
    {
        $key = 'PlentymarketsAdapter.' . $this->getClassName(get_called_class()) . '.LastChangeDateTime';

        $timezone = new DateTimeZone('UTC');
        $dateTime = new DateTimeImmutable('now', $timezone);

        $config->set($key, $dateTime);
    }
}
