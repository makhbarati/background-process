<?php

namespace Terminal42\BackgroundProcess\Forker;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DisownForker extends AbstractForker
{
    /**
     * {@inheritdoc}
     */
    public function run($configFile)
    {
        $commandline = sprintf(
            '%s %s >/dev/null 2>&1 </dev/null & disown',
            $this->executable,
            escapeshellarg($configFile)
        );

        return $this->startCommand($commandline);
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported()
    {
        try {
            (new Process("echo '' & disown"))->mustRun();
        } catch (ProcessFailedException $e) {
            return false;
        }

        return true;
    }
}
