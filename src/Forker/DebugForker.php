<?php

namespace Terminal42\BackgroundProcess\Forker;

use Symfony\Component\Process\Process;

class DebugForker extends AbstractForker
{

    /**
     * Executes a command.
     *
     * @param string $configFile
     */
    public function run($configFile)
    {
        $commandline = sprintf(
            '%s %s',
            $this->executable,
            escapeshellarg($configFile)
        );

        $process = new Process('exec '.$commandline, null, $this->env);

        $this->logger->info('Starting ' . $commandline);

        $process->start();

        sleep(1);

        if (!$process->isRunning()) {
            $this->logger->info(
                sprintf(
                    'Process is not running! Exit code %s (%s)',
                    $process->getExitCode(),
                    $process->getExitCodeText()
                )
            );

            return;
        }

        $process->wait();

        $this->logger->info(
            sprintf(
                'Process finished! Exit code %s (%s)',
                $process->getExitCode(),
                $process->getExitCodeText()
            )
        );
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
