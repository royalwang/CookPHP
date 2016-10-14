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

use \Helpers\File;

/**
 * 配制类
 * @author CookPHP <admin@cookphp.org>
 *
 */
class Config {

    static $_config = [];

    /**
     * 检测配置是否存在
     * @access public
     * @param string $key
     * @return bool
     */
    public static function has($key) {
        $keys = explode('.', $key);
        return isset(self::$_config[$keys[0]][$keys[1]]) ? true : false;
    }

    /**
     * 返回配制
     * 优先配制 框架、公共、项目
     * @access public
     * @param string $key
     * @param string    $type 配制类型
     * @return mixed
     */
    public static function get($key, $default = null, $type = 'php') {
        $keys = explode('.', $key);
        if (count($keys) > 1) {
            if (!self::has($key)) {
                self::parse([__COOK__ . 'Config' . DS . $keys[0], __COMMON__ . 'Config' . DS . $keys[0], __APP__ . Route::getProject() . DS . 'Config' . DS . $keys[0]], $keys[0], $type);
            }
            return self::$_config[$keys[0]][$keys[1]] ?? $default;
        }
        return $default;
    }

    /**
     * 设置配制
     * @access public
     * @param array|string $key
     * @param string    $range  作用域
     * @param mixed        $value
     */
    public static function set($key, $range, $value = null) {
        if (is_array($key)) {
            $keys = array_change_key_case($key, CASE_LOWER);
            foreach ($keys as $key => $value) {
                self::$_config[$range][$key] = $value;
            }
        } else {
            if (!empty($key)) {
                self::$_config[$range][$key] = $value;
            }
        }
    }

    /**
     * 返回所有配制
     * @param string $range 作用域
     * @param string    $type 配制类型
     * @return mixed
     */
    public static function all($range, $type = 'php') {
        if (!isset(self::$_config[$range])) {
            self::parse([__COOK__ . 'Config' . DS . $range, __COMMON__ . 'Config' . DS . $range, __APP__ . 'Config' . DS . $range], $range, $type);
        }
        return self::$_config[$range] ?? null;
    }

    /**
     * 解析配置文件或内容
     * @access public
     * @param string    $config 配置文件路径或内容
     * @param string    $range  作用域
     * @param string    $type 配制类型
     */
    public static function parse($config, $range, $type = null) {
        foreach ((array) $config as $value) {
            if (empty($type)) {
                $type = File::extension($value);
            } else {
                $value .= '.' . $type;
            }
            $build = Loader::initialize('\\Drivers\\Config\\' . ucwords($type));
            if ($build->supports($value)) {
                Log::setLog('config', 'read:' . $value, function () use ($build, $value, $range) {
                    self::set($build->read($value), $range);
                });
            }
        }
    }

}
