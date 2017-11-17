<?php

namespace Filesystem\Utils\Executor;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Class SymfonyExecutor
 * @package Filesystem\Utils\Executor
 */
class SymfonyExecutor implements ExecutorInterface
{
    private $timeout = null;

    private $idleTimeout = null;

    protected function getProcess($command)
    {
        $proc = new Process($command);

        $proc->setTimeout($this->timeout);

        $proc->setIdleTimeout($this->idleTimeout);

        return $proc;
    }

    public function execute($command, $throw = true)
    {
        $proc = $this->getProcess($command);

        try
        {
            $proc->mustRun();
        }
        catch (ProcessFailedException $e)
        {
            if($throw)
            {
                throw new ExecutorException($e->getMessage());
            }
        }

        return explode("\n", $proc->getOutput());
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    public function setIdleTimeout($timeout)
    {
        $this->idleTimeout = $timeout;
    }
}