<?php

namespace PlentymarketsAdapter\QueryBus;

use PlentyConnector\Connector\Config\ConfigInterface;

/**
 * Class ChangedDateTimeTrait.
 */
trait ChangedDateTimeTrait
{
    /**
     * @param ConfigInterface $config
     *
     * @return \DateTimeImmutable
     */
    public function getChangedDateTime(ConfigInterface $config)
    {
        $key = 'PlentymarketsAdapter.'.get_called_class().'DateTime';

        $timezone = new \DateTimeZone('UTC');
        $lastRun = $config->get($key, '2000-01-01');

        $dateTime = new \DateTimeImmutable($lastRun, $timezone);

        return $dateTime->format('Y-m-d G-i-s');
    }

    /**
     * @param ConfigInterface $config
     */
    public function setChangedDateTime(ConfigInterface $config)
    {
        $key = 'PlentymarketsAdapter.'.get_called_class().'DateTime';

        $timezone = new \DateTimeZone('UTC');
        $dateTime = new \DateTimeImmutable('now', $timezone);

        $config->set($key, $dateTime);
    }
}
