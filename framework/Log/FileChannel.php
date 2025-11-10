<?php

namespace Framework\Log;

class FileChannel
{
    protected string $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    /**
     * Writes a log message to the file.
     *
     * @param string $level   The log level.
     * @param string $message The log message.
     * @param array  $context Additional context information.
     *
     * @return void
     */
    public function write(string $level, string $message, array $context = []): void
    {
        $contextText = !empty($context) ? json_encode($context) : '';
        $line = sprintf("[%s] %s: %s %s\n", date('Y-m-d H:i:s'), strtoupper($level), $message, $contextText);
        file_put_contents($this->file, $line, FILE_APPEND);
    }
}
