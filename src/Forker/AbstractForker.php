<?php

namespace Terminal42\BackgroundProcess\Forker;

use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

abstract class AbstractForker implements ForkerInterface
{
    /**
     * @var array
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
     * @param array                $executable
     * @param array|null           $env
     * @param LoggerInterface|null $logger
     */
    public function __construct(array $executable = null, array $env = null, LoggerInterface $logger = null)
    {
        $this->executable = $executable ?: [__DIR__.'/../../bin/background-process'];
        $this->env = $env;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function setExecutable(array $executable)
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
     * @param array $commandline
     *
     * @return Process
     */
    protected function startCommand(array $commandline)
    {
        $process = new Process($commandline, null, $this->env);
        $process->setTimeout(null);
        $process->setIdleTimeout(null);

        if (null !== $this->logger) {
            $this->logger->info(
                'Starting "{commandline}" with {forker_class}',
                [
                    'commandline' => $process->getCommandLine(),
                    'forker_class' => get_called_class(),
                ]
            );
        }

        $process->start();

        usleep($this->timeout);

        if (null !== $this->logger && !$process->isRunning()) {
            $this->logger->error(
                'Process did not start correctly',
                [
                    'commandline' => $process->getCommandLine(),
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
