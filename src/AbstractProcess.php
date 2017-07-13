<?php

namespace Terminal42\BackgroundProcess;

use Terminal42\BackgroundProcess\Exception\InvalidJsonException;

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

    /**
     * @param string $filename
     *
     * @return array
     *
     * @throws InvalidJsonException
     */
    protected static function readConfig($filename)
    {
        if (!is_file($filename)) {
            throw new \InvalidArgumentException(sprintf('Config file "%s" does not exist.', $filename));
        }

        $content = file_get_contents($filename);
        $config = json_decode($content, true);

        if (!is_array($config)) {
            throw new InvalidJsonException($filename, $content);
        }

        return $config;
    }

    /**
     * @param string $filename
     * @param array  $config
     *
     * @throws \RuntimeException
     */
    protected static function writeConfig($filename, array $config)
    {
        if (false !== ($tmp = tempnam(dirname($filename), basename($filename)))) {
            $content = json_encode($config);

            if (file_put_contents($tmp, $content) === strlen($content)) {
                if (rename($tmp, $filename)) {
                    return;
                }
            }
        }

        throw new \RuntimeException(sprintf('Unable to write config file to %s', $filename));
    }
}
