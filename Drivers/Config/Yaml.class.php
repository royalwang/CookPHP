<?php

namespace Drivers\Config;

use \Interfaces\Config;

class Yaml implements Config {

    public function read($resource) {
        return \Yaml::parse(file_get_contents($resource));
    }

    public function supports($resource): bool {
        return is_string($resource) && 'yml' === pathinfo($resource, PATHINFO_EXTENSION) && is_file($resource);
    }

}
