<?php

namespace Drivers\Config;

use \Interfaces\Config;

class Php implements Config {

    public function read($resource) {
        return require($resource);
    }

    public function supports($resource): bool {
        return is_string($resource) && 'php' === pathinfo($resource, PATHINFO_EXTENSION) && is_file($resource);
    }

}
