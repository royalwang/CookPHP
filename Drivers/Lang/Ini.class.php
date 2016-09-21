<?php

namespace Drivers\Lang;

use \Interfaces\Lang;

class Ini implements Lang {

    public function read($resource) {
        return parse_ini_string(file_get_contents($resource), true, INI_SCANNER_RAW);
    }

    public function supports($resource): bool {
        return is_string($resource) && 'ini' === pathinfo($resource, PATHINFO_EXTENSION) && is_file($resource);
    }

}
