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
 * 输入操作类
 * @author CookPHP <admin@cookphp.org>
 */
class Input {

    /**
     * 获取GET
     * @access public
     * @param string|null $key 名称 为空时返回所有
     * @param string $filter 安全过滤方法
     * @return mixed
     */
    public static function get($key = null, $filter = null) {
        return is_null($key) ? self::varFilter($_GET, $filter) : (isset($_GET[$key]) ? self::varFilter($_GET[$key], $filter) : null);
    }

    /**
     * 获取POST
     * @access public
     * @param string|null $key 名称 为空时返回所有
     * @param string $filter 安全过滤方法
     * @return mixed
     */
    public static function post($key = null, $filter = null) {
        return is_null($key) ? self::varFilter($_POST, $filter) : (isset($_POST[$key]) ? self::varFilter($_POST[$key], $filter) : null);
    }

    /**
     * 获取PUT
     * @access public
     * @param string|null $key 名称 为空时返回所有
     * @param string $filter 安全过滤方法
     * @return mixed
     */
    public static function put($key = null, $filter = null) {
        static $_put = null;
        if ($_put === null) {
            $_put = parse_str(file_get_contents('php://input'), $_put);
        }
        return is_null($key) ? self::varFilter($_put, $filter) : (isset($_put[$key]) ? self::varFilter($_put[$key], $filter) : null);
    }

    /**
     * 初始HTTP_RAW_POST_DATA
     * 考虑到PHP7默认禁止 HTTP_RAW_POST_DATA 如微信支付时
     * @access public
     */
    public static function httpRawPostData() {
        if (Request::isPost() && empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $GLOBALS['HTTP_RAW_POST_DATA'] = file_get_contents("php://input");
        }
    }

    /**
     * 获取Ddlete
     * @access public
     * @param string|null $key 名称 为空时返回所有
     * @param string $filter 安全过滤方法
     * @return mixed
     */
    public static function delete($key = null, $filter = null) {
        static $_delete = null;
        if (is_null($_delete)) {
            parse_str(file_get_contents('php://input'), $_delete);
            $_delete = array_merge($_delete, $_GET);
        }
        return is_null($key) ? self::varFilter($_delete, $filter) : (isset($_delete[$key]) ? self::varFilter($_delete[$key], $filter) : null);
    }

    /**
     * 获取COOKIE
     * @access public
     * @param string|null $key 名称 为空时返回所有
     * @param string $filter 安全过滤方法
     * @return mixed
     */
    public static function cookie($key = null, $filter = null) {
        return is_null($key) ? self::varFilter($_COOKIE, $filter) : (isset($_COOKIE[$key]) ? self::varFilter($_COOKIE[$key], $filter) : null);
    }

    /**
     * 获取REQUEST
     * @access public
     * @param string|null $key 名称 为空时返回所有
     * @param string $filter 安全过滤方法
     * @return mixed
     */
    public static function request($key = null, $filter = null) {
        return is_null($key) ? self::varFilter($_REQUEST, $filter) : (isset($_REQUEST[$key]) ? self::varFilter($_REQUEST[$key], $filter) : null);
    }

    /**
     * 获取SERVER
     * @access public
     * @param string|null $key 名称 为空时返回所有
     * @param string $filter 安全过滤方法
     * @return mixed
     */
    public static function server($key = null, $filter = null) {
        return is_null($key) ? self::varFilter($_SERVER, $filter) : (isset($_SERVER[$key]) ? self::varFilter($_SERVER[$key], $filter) : null);
    }

    /**
     * 获取ENV
     * @access public
     * @param string|null $key 名称 为空时返回所有
     * @param string $filter 安全过滤方法
     * @return mixed
     */
    public static function env($key = null, $filter = null) {
        return is_null($key) ? self::varFilter($_ENV, $filter) : (isset($_ENV[$key]) ? self::varFilter($_ENV[$key], $filter) : null);
    }

    /**
     * 参数过滤方法
     * @access public
     * @param string|array $content 过滤内容
     * @param string $filter 过滤方法
     * @return mixed
     */
    public static function varFilter($content, $filter = null) {
        static $_filter = null;
        if ($_filter === null) {
            $_filter = Config::get('input.filter');
        }
        return is_array($content) ? array_map(function ($content) {
                    return self::varFilter($content);
                }, $content) : trim($_filter($content));
    }

    /**
     * 当前请求的参数
     * @access public
     * @param string $name 变量名
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function param($name = '', $default = null) {
        static $param = null;
        if (empty($param)) {
            switch (Request::method()) {
                case 'POST':
                    $vars = self::post();
                    break;
                case 'PUT':
                    $vars = self::put();
                    break;
                case 'DELETE':
                    $vars = self::delete();
                    break;
                default:
                    $vars = [];
            }
            $param = array_merge(self::get(), $vars);
        }
        return $name ? ($param[$name] ?? $default) : ($param ?: $default);
    }

}
