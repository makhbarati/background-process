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
     * @var array|null
     */
    protected $env;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var int
     */
    private $timeout = 500;

    /**
     * Constructor.
     *
     * @param string               $executable
     * @param array|null           $env
     * @param LoggerInterface|null $logger
     */
    public function __construct($executable = null, array $env = null, LoggerInterface $logger = null)
    {
        $this->executable = $executable ?: escapeshellarg(__DIR__.'/../../bin/background-process');
        $this->env = $env;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function setExecutable($executable)
    {
        $this->executable = $executable;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExecutable()
    {
        return $this->executable;
    }

    /**
     * {@inheritdoc}
     */
    public function setTimeout($timeout)
    {
        $this->timeout = (int) $timeout;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeout()
    {
        return $this->timeout;
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

        $process = new Process($commandline, null, $this->env);
        $process->setTimeout(null);
        $process->setIdleTimeout(null);

        $process->start();

        usleep($this->timeout);

        if (null !== $this->logger && !$process->isRunning()) {
            $this->logger->error(
                'Process did not start correctly',
                [
                    'commandline' => $commandline,
                    'forker_class' => get_called_class(),
                    'exit_code' => $process->getExitCode(),
                    'exit_text' => $process->getExitCodeText(),
                    'stopped' => $process->hasBeenStopped(),
                    'signaled' => $process->hasBeenSignaled(),
                    'stopsignal' => $process->getStopSignal(),
                    'termsignal' => $process->getTermSignal(),
                ]
            );
        }

        return $process;
    }
}
