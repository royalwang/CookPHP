<?php

define('START_TIME', microtime(true));
define('START_MEMORY', memory_get_usage());
define('COOK_VERSION', '0.0.1');
defined('APP_DEBUG') or define('APP_DEBUG', false);
defined('CHARSET') or define('CHARSET', 'utf-8');
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('CONF_TYPE') or define('CONF_TYPE', 'php');
defined('__COOK__') or define('__COOK__', __DIR__ . DS);
defined('__APP__') or define('__APP__', dirname(__COOK__) . DS . 'App' . DS);
defined('__COMMON__') or define('__COMMON__', __APP__ . 'Common' . DS);
defined('__STORAGE__') or define('__STORAGE__', __COMMON__ . 'Runtime' . DS . 'Storage' . DS);
defined('__TMP__') or define('__TMP__', __STORAGE__ . 'Tmp' . DS);
defined('__CACHE__') or define('__CACHE__', __STORAGE__ . 'Cache' . DS);
defined('__LOGS__') or define('__LOGS__', __STORAGE__ . 'Logs' . DS);

if (empty($_GET) && empty($_POST) && isset($_SERVER['argv'][1])) {
    if (isset($_SERVER['argv'][2])) {
        parse_str($_SERVER['argv'][2], $_GET);
        parse_str($_SERVER['argv'][2], $_REQUEST);
    }
    defined('IS_CLI') or define('IS_CLI', true);
}
defined('IS_CLI') or define('IS_CLI', false);
