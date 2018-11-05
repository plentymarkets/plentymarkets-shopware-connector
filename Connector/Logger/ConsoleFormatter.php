<?php

namespace SystemConnector\Logger;

use Monolog\Formatter\LineFormatter;
use Monolog\Logger;

/**
 * Formats incoming records for console output by coloring them depending on log level.
 *
 * @author Tobias Schultze <http://tobion.de>
 */
class ConsoleFormatter extends LineFormatter
{
    const SIMPLE_FORMAT = "%start_tag%[%datetime%] %channel%.%level_name%:%end_tag% %message% %context% %extra%\n";

    /**
     * {@inheritdoc}
     */
    public function __construct(
        $format = null,
        $dateFormat = null,
        $allowInlineLineBreaks = false,
        $ignoreEmptyContextAndExtra = true
    ) {
        parent::__construct($format, $dateFormat, $allowInlineLineBreaks, $ignoreEmptyContextAndExtra);
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        if ($record['level'] >= Logger::ERROR) {
            $record['start_tag'] = '<error>';
            $record['end_tag'] = '</error>';
        } elseif ($record['level'] >= Logger::NOTICE) {
            $record['start_tag'] = '<comment>';
            $record['end_tag'] = '</comment>';
        } elseif ($record['level'] >= Logger::INFO) {
            $record['start_tag'] = '<info>';
            $record['end_tag'] = '</info>';
        } else {
            $record['start_tag'] = '';
            $record['end_tag'] = '';
        }

        return parent::format($record);
    }
}
