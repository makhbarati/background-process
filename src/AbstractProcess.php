<?php

namespace Terminal42\BackgroundProcess;

abstract class AbstractProcess
{
    protected $setFile;
    protected $getFile;
    protected $inputFile;
    protected $outputFile;
    protected $errorOutputFile;

    /**
     * Constructor.
     *
     * @param string $uuid
     * @param string $workDir
     *
     * @throws \InvalidArgumentException If the working directory does not exist
     */
    public function __construct($uuid, $workDir)
    {
        $workDir = realpath(rtrim($workDir, '/'));

        if (false === $workDir) {
            throw new \InvalidArgumentException(
                sprintf('Working directory "%s" does not exist.', $workDir)
            );
        }

        $this->setFile = $workDir.'/'.$uuid.'.set.json';
        $this->getFile = $workDir.'/'.$uuid.'.get.json';
        $this->inputFile = $workDir.'/'.$uuid.'.in.log';
        $this->outputFile = $workDir.'/'.$uuid.'.out.log';
        $this->errorOutputFile = $workDir.'/'.$uuid.'.err.log';
    }

    protected static function readConfig($file)
    {
        if (!is_file($file)) {
            throw new \InvalidArgumentException(sprintf('Config file "%s" does not exist.', $file));
        }

        $config = json_decode(file_get_contents($file), true);

        if (!is_array($config)) {
            throw new \InvalidArgumentException(sprintf('Config file "%s" does not contain valid JSON.', $file));
        }

        return static::validateConfig($config);
    }

    protected static function validateConfig(array $config)
    {
        if (!isset($config['uuid'])) {
            throw new \InvalidArgumentException('Missing property "uuid" in config file.');
        }

        return $config;
    }
}
