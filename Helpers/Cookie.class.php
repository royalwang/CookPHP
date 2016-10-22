<?php

/**
 * CookPHP framework
 *
 * @name CookPHP framework
 * @package CookPHP
 * @author CookPHP <admin@cookphp.org>
 * @version 0.0.1 Beta
 * @link http://www.cookphp.org
 * @copyright cookphp.org
 * @license <a href="http://www.cookphp.org">CookPHP</a>
 */

namespace Helpers;

use \Core\Config;

/**
 * Cookie处理类
 * @author 费尔 <admin@cookphp.org>
 */
class Cookie {

    private static $config;

    /**
     * 获取COOKIE
     * @access public
     * @param string|null $name 名称 为空时返回所有
     * @return mixed|null
     */
    public static function get($name) {
        $value = $_COOKIE[$name] ?? '';
        if (substr($value, 0, 8) === 'cookphp:') {
            $value = substr($value, 8);
            return array_map('urldecode', json_decode($value, true));
        } else {
            return $value;
        }
    }

    /**
     * 设置 Cookie
     * @access public
     * @param string $name
     * @param mixed $value
     * @param array $option
     */
    public static function set($name, $value = '', $option = []) {
        $lifetime = !empty(self::$config['lifetime']) ? time() + intval(self::$config['lifetime']) : 0;
        if (is_array($value)) {
            $value = 'cookphp:' . json_encode(array_map('urlencode', $value));
        }
        self::edit($name, $value, $lifetime, $option);
        $_COOKIE[$name] = $value;
    }

    /**
     * 删除 Cookie
     * @access public
     * @param string $name
     * @param array $option
     */
    public static function rm($name, $option = []) {
        self::edit($name, '', time() - 3600, $option);
        unset($_COOKIE[$name]);
    }

    /**
     * 清除所有 Cookie
     * @access public
     * @access public
     * @return boolean
     */
    public static function clear($option = []) {
        if (empty($_COOKIE)) {
            return null;
        }
        foreach ($_COOKIE as $key => $val) {
            self::edit($key, '', time() - 3600, $option);
            unset($_COOKIE[$key]);
        }
        return null;
    }

    /**
     * 设置
     * @access private
     * @param array $option
     */
    private static function edit($name, $value = "", $lifetime = 0, $option = []) {
        self::init($option);
        setcookie($name, $value, $lifetime, self::$config['path'], self::$config['domain'], self::$config['secure'], self::$config['httponly']);
    }

    /**
     * 初始化配制
     * @access private
     * @param array $option
     */
    private static function init($option = []) {
        if (empty(self::$config)) {
            self::$config = [
                'lifetime' => $option['lifetime'] ?? Config::get('cookie.lifetime'),
                'path' => $option['path'] ?? Config::get('cookie.path'),
                'domain' => $option['domain'] ?? Config::get('cookie.domain'),
                'secure' => $option['secure'] ?? Config::get('cookie.secure'),
                'httponly' => $option['httponly'] ?? Config::get('cookie.httponly')];
        }
    }

}
