<?php

namespace Terminal42\BackgroundProcess\Forker;

interface ForkerInterface
{
    /**
     * @param string $executable
     */
    public function setExecutable($executable);

    /**
     * @return string
     */
    public function getExecutable();

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
