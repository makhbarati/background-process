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

    /**
     * @param string $filename
     *
     * @return array
     * @throws \RuntimeException
     */
    protected static function readConfig($filename)
    {
        if (!is_file($filename)) {
            throw new \InvalidArgumentException(sprintf('Config file "%s" does not exist.', $filename));
        }

        $fp = fopen($filename, 'rb');

        if (!flock($fp, LOCK_SH)) {
            throw new \RuntimeException(sprintf('Failed to aquire lock for "%s"', $filename));
        }

        $content = fread($fp, filesize($filename));
        flock($fp, LOCK_UN);
        fclose($fp);

        $config = json_decode($content, true);

        if (!is_array($config)) {
            throw new \InvalidArgumentException(sprintf('Config file "%s" does not contain valid JSON.', $filename));
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
        $fp = fopen($filename, 'cb');

        if (!flock($fp, LOCK_EX)) {
            throw new \RuntimeException(sprintf('Failed to aquire lock for "%s"', $filename));
        }

        ftruncate($fp, 0);
        fwrite($fp, json_encode($config));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}
