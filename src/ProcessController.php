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
    private $forkers;

    /**
     * Constructor.
     *
     * @param array  $config
     * @param string $workDir
     */
    public function __construct(array $config, $workDir)
    {
        $this->config = static::validateConfig($config);

        parent::__construct($this->config['uuid'], $workDir);
    }


    public function addForker(ForkerInterface $forker)
    {
        $this->forkers[] = $forker;
    }

    public function start()
    {
        $forker = $this->getForker();

        $forker->run(__DIR__.'/../bin/background-process '.$this->setFile);
    }

    public function getPid()
    {
        return $this->config['pid'];
    }

    public function getExitCode()
    {
        return $this->config['exitcode'];
    }

    public function getStatus()
    {
        return $this->config['status'];
    }

    public function stop()
    {
        $this->config['stop'] = true;
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

    private function getForker()
    {
        foreach ($this->forkers as $forker) {
            if ($forker->isSupported()) {
                return $forker;
            }
        }

        throw new \RuntimeException('No forker found for your current platform.');
    }

    private function updateStatus()
    {
        if (Process::STATUS_STARTED !== $this->config['status']) {
            return;
        }

        $this->config = array_merge($this->config, static::readConfig($this->getFile));

        if (Process::STATUS_STARTED !== $this->config['status']) {
            //$this->close();
        }
    }

    private function close()
    {
        unlink($this->setFile);
        unlink($this->getFile);
        unlink($this->inputFile);
        unlink($this->outputFile);
        unlink($this->errorOutputFile);
    }

    public static function create($workDir, $commandline, $cwd = null)
    {
        return new static(
            [
                'uuid' => 'foo', // TODO create uuid
                'status' => Process::STATUS_READY,

                'commandline' => $commandline,
                'cwd' => $cwd ?: getcwd(),
            ],
            $workDir
        );
    }

    public static function restore($file)
    {
        return new static(static::readConfig($file), dirname($file));
    }
}
