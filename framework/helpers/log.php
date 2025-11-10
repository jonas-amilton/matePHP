<?php

use Framework\Log\Logger;

/**
 * Logs a message with a given level and optional channel.
 *
 * @param string      $level   The log level (e.g., 'info', 'error', 'debug').
 * @param string      $message The message to log.
 * @param string|null $channel Optional channel name.
 *
 * @return void
 */
function logger(string $level, string $message, ?string $channel = null)
{
    $logger = Logger::getInstance();
    if ($channel) {
        $logger->channel($channel)->log($level, $message);
    } else {
        $logger->log($level, $message);
    }
}
