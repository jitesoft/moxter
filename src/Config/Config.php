<?php
namespace Jitesoft\Moxter\Config;

use Jitesoft\Moxter\Contracts\ConfigInterface;

/**
 * Class Config
 *
 * Configuration class using environment variables for fetching configurations.
 */
final class Config implements ConfigInterface {

    public function get(string $name, $default = null) {
        return $this->{$name} ?? $default;
    }

    public function __get(string $name) {
        if (isset($_ENV[$name])) {
            return $_ENV[$name];
        }
        return null;
    }
}
