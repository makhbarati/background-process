<?php

namespace Terminal42\BackgroundProcess\Forker;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class WindowsStartForker extends AbstractForker
{
    /**
     * {@inheritdoc}
     */
    public function run($configFile)
    {
        $commandline = sprintf(
            'start /b %s %s 2>&1 >nul <nul',
            $this->executable,
            escapeshellarg($configFile)
        );

        $this->startCommand($commandline);
    }

    /**
     * {@inheritdoc}
     */
     public function isSupported()
     {
         if ('\\' !== DIRECTORY_SEPARATOR) {
           return false;
         }

         try {
             (new Process('start /b dir', null, $this->env))->mustRun();
         } catch (ProcessFailedException $e) {
             return false;
         }

         return true;
     }
}
