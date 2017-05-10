<?php

namespace Terminal42\BackgroundProcess\Forker;

use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

abstract class AbstractForker implements ForkerInterface
{
    /**
     * @var string
     */
    protected $executable;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param string               $executable
     * @param LoggerInterface|null $logger
     */
    public function __construct($executable = null, LoggerInterface $logger = null)
    {
        $this->executable = $executable ?: escapeshellarg(__DIR__.'/../../bin/background-process');
        $this->logger = $logger;
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
     * @param string $commandline
     */
    protected function startCommand($commandline)
    {
        if (null !== $this->logger) {
            $this->logger->info(
                'Starting "{commandline}" with {forker_class}',
                [
                    'commandline' => $commandline,
                    'forker_class' => get_called_class(),
                ]
            );
        }

        (new Process($commandline))->start();
    }
}
