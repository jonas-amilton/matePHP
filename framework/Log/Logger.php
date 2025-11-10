<?php

namespace Framework\Log;

class Logger
{
    private static ?self $instance = null;
    private array $config;
    private array $channels = [];

    private function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Gets the singleton instance of the Logger.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (!self::$instance) {
            $config = require __DIR__ . '/logging.php';
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    /**
     * Retrieves a logging channel by name.
     *
     * @param string|null $name The name of the channel.
     *
     * @return self
     */
    public function channel(?string $name = null): self
    {
        $name = $name ?? $this->config['default'];

        if (!isset($this->channels[$name])) {
            $this->channels[$name] = $this->createChannel($name);
        }

        return $this->channels[$name];
    }

    /**
     * Creates a logging channel instance.
     *
     * @param string $name The name of the channel.
     *
     * @return self
     *
     * @throws \Exception If the channel is not configured.
     */
    private function createChannel(string $name): self
    {
        if (!isset($this->config['channels'][$name])) {
            throw new \Exception("Channel {$name} not configured.");
        }
        $channel = new self(['default' => $name, 'channels' => [$name => $this->config['channels'][$name]]]);
        return $channel;
    }

    /**
     * Logs a message at the specified level.
     *
     * @param string $level   The log level.
     * @param string $message The log message.
     *
     * @return void
     */
    public function log(string $level, string $message): void
    {
        $channelName = $this->config['default'];
        $channelConfig = $this->config['channels'][$channelName];

        $formatted = sprintf("[%s] %s: %s\n", date('Y-m-d H:i:s'), strtoupper($level), $message);

        if ($channelConfig['driver'] === 'single') {
            file_put_contents($channelConfig['path'], $formatted, FILE_APPEND);
        } elseif ($channelConfig['driver'] === 'stdout') {
            echo $formatted;
        }
    }

    public function info(string $msg)
    {
        $this->log('info', $msg);
    }
    public function debug(string $msg)
    {
        $this->log('debug', $msg);
    }
    public function error(string $msg)
    {
        $this->log('error', $msg);
    }
}
