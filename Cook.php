<?php

/**
 * CookPHP Framework
 *
 * @name CookPHP Framework
 * @package CookPHP
 * @author CookPHP <admin@cookphp.org>
 * @version 1.0 Beta
 * @link http://www.cookphp.org
 * @copyright cookphp.org
 * @license <a href="http://www.cookphp.org">CookPHP</a>
 */
version_compare(PHP_VERSION, '7.0.0', 'ge') or die('require PHP >= 7.0.0 !');
require (__DIR__ . DIRECTORY_SEPARATOR . 'Constants.php');
if (!class_exists('\Core\Loader')) {
    require (__COOK__ . 'Core' . DIRECTORY_SEPARATOR . 'Loader.class.php');
    \Core\Loader::register();
}
\Core\Route::init();
