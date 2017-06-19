<?php

namespace Terminal42\BackgroundProcess\Forker;

interface ForkerInterface
{
    /**
     * Sets the executable with arguments to use for the background process.
     *
     * @param array $executable
     */
    public function setExecutable(array $executable);

    /**
     * Gets the executable with arguments to use for the background process.
     *
     * @return array
     */
    public function getExecutable();

    /**
     * Sets the timeout in milliseconds to wait after starting a process.
     *
     * @param int $timeout
     *
     * @return $this
     */
    public function setTimeout($timeout);

    /**
     * Gets the timeout in milliseconds to wait after starting a process.
     *
     * @return int
     */
    public function getTimeout();

    /**
     * Executes a command.
     *
     * @param string $configFile
     */
    public function run($configFile);

    /**
     * Returns whether this forker is supported on the current platform.
     *
     * @return bool
     */
    public function isSupported();
}
