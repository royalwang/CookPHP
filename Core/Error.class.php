<?php

/**
 * CookPHP Framework
 *
 * @name CookPHP Framework
 * @package CookPHP
 * @author CookPHP <admin@cookphp.org>
 * @version 0.0.1 Beta
 * @link http://www.cookphp.org
 * @copyright cookphp.org
 * @license <a href="http://www.cookphp.org">CookPHP</a>
 */

namespace Core;

/**
 * 错误处理类
 * @author CookPHP <admin@cookphp.org>
 */
class Error {

    /**
     * 错误处理程序
     * @access public
     * @return void
     */
    public static function show($message, $status_code = 500, $heading = 'An Error Was Encountered') {
        echo $message;
        exit();
    }

    /**
     * SQL错误
     * @access public
     * @param string $message
     */
    public static function sql($message) {
        echo $message;
        exit();
    }


}
