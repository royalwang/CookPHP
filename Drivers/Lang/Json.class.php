<?php

namespace Drivers\Lang;

use \Interfaces\Lang;

class Json implements Lang {

    public function read($resource) {
        return json_decode(file_get_contents($resource), true);
    }

    public function supports($resource): bool {
        return is_string($resource) && 'json' === pathinfo($resource, PATHINFO_EXTENSION) && is_file($resource);
    }

}
