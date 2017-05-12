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
     * @param string $id
     * @param string $workDir
     *
     * @throws \InvalidArgumentException If the working directory does not exist
     */
    public function __construct($id, $workDir)
    {
        $workDir = realpath(rtrim($workDir, '/'));

        if (false === $workDir) {
            throw new \InvalidArgumentException(
                sprintf('Working directory "%s" does not exist.', $workDir)
            );
        }

        $this->setFile = $workDir.'/'.$id.'.set.json';
        $this->getFile = $workDir.'/'.$id.'.get.json';
        $this->inputFile = $workDir.'/'.$id.'.in.log';
        $this->outputFile = $workDir.'/'.$id.'.out.log';
        $this->errorOutputFile = $workDir.'/'.$id.'.err.log';
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

        /*if (!isset($config['id'])) {
            throw new \InvalidArgumentException('Missing property "id" in config file.');
        }*/

        return $config;
    }
}
