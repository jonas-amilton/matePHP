<?php

namespace Framework\Console;

interface Command
{
    public function handle(array $args): void;
}
