<?php

namespace Terminal42\BackgroundProcess\Forker;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class NohupForker implements ForkerInterface
{
    /**
     * @var string
     */
    private $executable;

    /**
     * Constructor.
     *
     * @param string $executable
     */
    public function __construct($executable = null)
    {
        $this->executable = $executable ?: escapeshellarg(__DIR__.'/../../bin/background-process');
    }

    /**
     * @param string $executable
     */
    public function setExecutable($executable)
    {
        $this->executable = $executable;
    }

    /**
     * @return string
     */
    public function getExecutable()
    {
        return $this->executable;
    }

    /**
     * Executes a command.
     *
     * @param string $configFile
     *
     * @throws ProcessFailedException
     */
    public function run($configFile)
    {
        $commandline = sprintf(
            'nohup %s %s >/dev/null 2>&1 &',
            $this->executable,
            escapeshellarg($configFile)
        );

        (new Process($commandline))->start();
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
