<?php
namespace Jitesoft\Moxter\Config;

use function getenv;
use Jitesoft\Moxter\Contracts\ConfigInterface;

/**
 * Class Config
 *
 * Configuration class using environment variables for fetching configurations.
 */
final class Config implements ConfigInterface {

    public function get(string $name, $default = null, ?callable $cast = null) {
        $result = $this->{$name};
        if ($result !== null && $cast !== null) {
            return $cast($result);
        }

        return $result ?? $default;
    }

    public function __get(string $name) {
        if (isset($_ENV[$name])) {
            return $_ENV[$name];
        }

        return null;
    }

}
