<?php

namespace Terminal42\BackgroundProcess\Forker;

interface ForkerInterface
{
    /**
     * Executes a command.
     *
     * @param string $command
     */
    public function run($command);

    /**
     * Returns whether this forker is supported on the current platform.
     *
     * @return bool
     */
    public function isSupported();
}
