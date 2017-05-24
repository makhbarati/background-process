<?php

namespace Terminal42\BackgroundProcess\Forker;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class BashForker extends AbstractForker
{
    /**
     * {@inheritdoc}
     */
    public function run($configFile)
    {
        $commandline = sprintf(
            '%s %s >/dev/null 2>&1 </dev/null',
            $this->executable,
            escapeshellarg($configFile)
        );

        $this->startCommand('exec nohup bash -c '.escapeshellarg($commandline));
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported()
    {
        try {
            (new Process("exec nohup bash --version", null, $this->env))->mustRun();
        } catch (ProcessFailedException $e) {
            return false;
        }

        return true;
    }
}
