<?php

namespace Terminal42\BackgroundProcess\Forker;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class NohupForker extends AbstractForker
{
    /**
     * {@inheritdoc}
     */
    public function run($configFile)
    {
        $commandline = $this->executable;
        array_unshift($commandline, 'nohup');
        array_push($commandline, $configFile, '>/dev/null', '</dev/null', '2>&1', '&');

        $this->startCommand($commandline);
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported()
    {
        try {
            (new Process('nohup ls', null, $this->env))->mustRun();
        } catch (ProcessFailedException $e) {
            return false;
        }

        return true;
    }
}
