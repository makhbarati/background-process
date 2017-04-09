<?php

namespace Terminal42\BackgroundProcess;

use Symfony\Component\Process\Process;

class ProcessRunner extends AbstractProcess
{
    /**
     * @var Process
     */
    private $process;

    private $stdin;
    private $stdout;
    private $stderr;

    /**
     * Constructor.
     *
     * @param string $configFile
     */
    public function __construct($configFile)
    {
        $config = static::readConfig($configFile);

        parent::__construct($config['uuid'], dirname($configFile));

        $commandline = isset($config['commandline']) ? $config['commandline'] : '';
        $cwd = isset($config['cwd']) ? $config['cwd'] : null;

        $this->process = new Process($commandline, $cwd);

        $this->loadConfig($config);
    }

    public function run($interval = 1)
    {
        $this->start();

        return $this->wait($interval);
    }

    public function start()
    {
        if ($this->process->isStarted()) {
            return;
        }

        if (is_file($this->inputFile)) {
            $this->stdin = fopen($this->inputFile, 'rb');
            $this->process->setInput($this->stdin);
        }

        $this->process->start(
            function ($type, $data) {
                if (Process::OUT === $type) {
                    $this->addOutput($data);
                } else {
                    $this->addErrorOutput($data);
                }
            }
        );

        $this->saveConfig();
    }

    public function wait($interval)
    {
        do {
            usleep($interval * 1000000);

            $this->process->checkTimeout();
            $running = $this->process->isRunning();

            $config = $this->loadConfig();

            if ($running && $config['stop']) {
                $this->process->stop();
            }

            $this->saveConfig();
        } while ($running);

        $this->close();

        return $this->process->getExitCode();
    }

    public function stop($timeout = 10)
    {
        $this->process->stop($timeout);

        $this->saveConfig();
        $this->close();
    }

    private function close()
    {
        if (is_resource($this->stdin)) {
            fclose($this->stdin);
        }

        if (is_resource($this->stdout)) {
            fclose($this->stdout);
        }

        if (is_resource($this->stderr)) {
            fclose($this->stderr);
        }
    }

    public function addOutput($line)
    {
        if (!is_resource($this->stdout)) {
            $this->stdout = fopen($this->outputFile, 'wb');
        }

        fwrite($this->stdout, $line);
    }

    public function addErrorOutput($line)
    {
        if (!is_resource($this->stderr)) {
            $this->stderr = fopen($this->errorOutputFile, 'wb');
        }

        fwrite($this->stderr, $line);
    }

    private function loadConfig(array $config = null)
    {
        if (null === $config) {
            $config = static::readConfig($this->setFile);
        }

        $props = [
            'timeout' => 'setTimeout',
            'idleTimeout' => 'setIdleTimeout',
        ];

        foreach ($props as $key => $setter) {
            if (isset($config[$key])) {
                $this->process->{$setter}($config[$key]);
            }
        }

        return $config;
    }

    private function saveConfig()
    {
        $config = [
            'timeout' => $this->process->getTimeout(),
            'idleTimeout' => $this->process->getIdleTimeout(),

            'pid' => $this->process->getPid(),
            'exitcode' => $this->process->getExitCode(),
            'status' => $this->process->getStatus(),
        ];

        file_put_contents($this->getFile, json_encode($config));
    }
}
