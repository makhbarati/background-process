<?php

namespace Terminal42\BackgroundProcess;

use Symfony\Component\Process\Process;
use Terminal42\BackgroundProcess\Forker\ForkerInterface;

class ProcessController extends AbstractProcess
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var ForkerInterface[]
     */
    private $forkers = [];

    /**
     * Constructor.
     *
     * @param array                $config
     * @param string               $workDir
     *
     * @throws \InvalidArgumentException If the working directory does not exist
     */
    public function __construct(array $config, $workDir)
    {
        if (!isset($config['status'])) {
            $config['status'] = Process::STATUS_READY;
        }

        $this->config = $config;

        parent::__construct($this->config['id'], $workDir);
    }

    public function addForker(ForkerInterface $forker)
    {
        $this->forkers[] = $forker;
    }

    public function start()
    {
        $this->saveConfig();

        $this->config['status'] = Process::STATUS_STARTED;

        $forker = $this->getForker();

        $forker->run($this->setFile);
    }

    public function getPid()
    {
        $this->updateStatus();

        return $this->config['pid'];
    }

    public function getExitCode()
    {
        $this->updateStatus();

        return $this->config['exitcode'];
    }

    public function isRunning()
    {
        return Process::STATUS_STARTED === $this->getStatus();
    }

    public function isStarted()
    {
        return Process::STATUS_READY !== $this->getStatus();
    }

    public function isTerminated()
    {
        return Process::STATUS_TERMINATED === $this->getStatus();
    }

    public function getStatus()
    {
        $this->updateStatus();

        return $this->config['status'];
    }

    public function stop()
    {
        $this->config['stop'] = true;
    }

    public function delete()
    {
        if (!$this->isTerminated()) {
            throw new \LogicException('Cannot delete a running process.');
        }

        $this->close();
    }

    public function getCommandLine()
    {
        return $this->config['commandline'];
    }

    public function setCommandLine($commandline)
    {
        $this->config['commandline'] = $commandline;
    }

    public function setWorkingDirectory($cwd)
    {
        $this->config['cwd'] = $cwd;
    }

    public function getOutput()
    {
        if (!is_file($this->outputFile)) {
            return '';
        }

        return file_get_contents($this->outputFile);
    }

    public function getErrorOutput()
    {
        if (!is_file($this->errorOutputFile)) {
            return '';
        }

        return file_get_contents($this->errorOutputFile);
    }

    public function setTimeout($timeout)
    {
        $this->config['timeout'] = $timeout;
    }

    public function setIdleTimeout($timeout)
    {
        $this->config['idleTimeout'] = $timeout;
    }

    private function getForker()
    {
        foreach ($this->forkers as $forker) {
            if ($forker->isSupported()) {
                return $forker;
            }
        }

        throw new \RuntimeException('No forker found for your current platform.');
    }

    private function saveConfig()
    {
        file_put_contents($this->setFile, json_encode($this->config));
    }

    private function updateStatus()
    {
        if (Process::STATUS_STARTED !== $this->config['status']) {
            return;
        }

        if (is_file($this->getFile)) {
            $this->config = array_merge($this->config, static::readConfig($this->getFile));
        }
    }

    private function close()
    {
        @unlink($this->setFile);
        @unlink($this->getFile);
        @unlink($this->inputFile);
        @unlink($this->outputFile);
        @unlink($this->errorOutputFile);
    }

    public static function create($workDir, $commandline, $cwd = null, $id = null)
    {
        return new static(
            [
                'id' => $id ?: md5(uniqid('', true)),
                'commandline' => $commandline,
                'cwd' => $cwd ?: getcwd(),
            ],
            $workDir
        );
    }

    public static function restore($workDir, $id)
    {
        $config = static::readConfig($workDir.'/'.$id.'.set.json');

        if (is_file($getFile = $workDir.'/'.$id.'.get.json')) {
            $config = array_merge($config, static::readConfig($getFile));
        }

        return new static($config, $workDir);
    }
}
