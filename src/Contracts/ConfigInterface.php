<?php
namespace Jitesoft\Moxter\Contracts;

/**
 * Interface ConfigInterface
 *
 * Interface for configuration objects.
 */
interface ConfigInterface {

    /**
     * Fetch config using its key.
     *
     * @param string $name
     * @param null $default
     * @return mixed
     */
    public function get(string $name, $default = null);

}
