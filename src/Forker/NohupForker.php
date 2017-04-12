<?php

namespace Terminal42\BackgroundProcess\Forker;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class NohupForker implements ForkerInterface
{

    /**
     * Executes a command.
     *
     * @param string $command
     *
     * @throws ProcessFailedException
     */
    public function run($command)
    {
        (new Process('nohup ' . $command . ' >/dev/null 2>&1 &'))->start();
    }

    /**
     * Returns whether this forker is supported on the current platform.
     *
     * @return bool
     */
    public function isSupported()
    {
        try {
            (new Process('nohup ls'))->mustRun();
        } catch (ProcessFailedException $e) {
            return false;
        }

        return true;
    }
}
