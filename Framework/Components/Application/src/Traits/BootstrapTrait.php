<?php

declare(strict_types=1);

trait BootstrapTrait
{
    /**
     * DSefault settings uses a common basic structur which defines the parameters
     * for components within this framework which exposes configurable
     * parameters. This little snippet helps us to load the default settings which
     * can be override by the use app config yaml files.
     *
     * @param array $config
     * @return mixed
     */
    private function getDefaultSettings(array $config)
    {
        if (count($config) > 0) {
            if (array_key_exists('drivers', $config)) {
                $defaultDriver = $config['default_driver'];
                if (array_key_exists($defaultDriver, $config['drivers'])) {
                    $selectedDriver = $config['drivers'][$defaultDriver];
                    if ($selectedDriver['default'] === true) {
                        return $selectedDriver['class'];
                    }
                }
            }
        }
    }
}
