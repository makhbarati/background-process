<?php

namespace Terminal42\BackgroundProcess\Forker;

class InlineForker extends AbstractForker
{

    /**
     * Executes a command.
     *
     * @param string $configFile
     */
    public function run($configFile)
    {
        $commandline = $this->executable;
        array_push($commandline, $configFile);

        $process = $this->startCommand($commandline);
        $process->wait();
    }

    /**
     * Returns whether this forker is supported on the current platform.
     *
     * @return bool
     */
    public function isSupported()
    {
        return true;
    }
}
