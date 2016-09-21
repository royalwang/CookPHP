<?php

namespace Drivers\Config;

use \Interfaces\Config;

class Env implements Config {

    public function read($resource) {
        $list = parse_ini_string(file_get_contents($resource), true, INI_SCANNER_RAW);
        if (!empty($list)) {
            $this->load($list);
        }
        return $list;
    }

    public function load($resource) {
        foreach ($resource as $name => $value) {
            putenv("$name=$value");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }

    public function supports($resource): bool {
        return is_string($resource) && 'env' === pathinfo($resource, PATHINFO_EXTENSION) && is_file($resource);
    }

}
