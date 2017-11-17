<?php

namespace Filesystem\Utils\Executor;

interface ExecutorInterface
{
    public function execute($command, $throw = true);
}