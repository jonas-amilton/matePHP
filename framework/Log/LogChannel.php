<?php

namespace Framework\Log;

interface LogChannel
{
    public function log(string $level, string $message, array $context = []): void;
}
