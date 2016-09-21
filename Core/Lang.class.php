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

namespace Core;

use \Helpers\File;

/**
 * 语言类
 * @author CookPHP <admin@cookphp.org>
 *
 */
class Lang {

    static $_lang = [];

    /**
     * 检测配置是否存在
     * @access public
     * @param string $key
     * @return bool
     */
    public static function has($key) {
        $keys = explode('.', $key);
        return isset(self::$_lang[$keys[0]][$keys[1]]) ? true : false;
    }

    /**
     * 返回语言
     * 优先语言 框架、公共、项目
     * @access public
     * @param string $key
     * @param array $replacer
     * @param string    $type 语言类型
     * @return mixed
     */
    public static function get($key, $replacer = [], $type = 'php') {
        $keys = explode('.', $key);
        $language = Config::get('lang.language');
        if (count($keys) > 1) {
            if (!self::has($key)) {
                self::parse([__COOK__ . 'Lang' . DS . $language . DS . $keys[0], __COMMON__ . 'Lang' . DS . $language . DS . $keys[0], __APP__ . Route::getProject() . DS . 'Lang' . DS . $language . DS . $keys[0]], $keys[0], $type);
            }
            $string = self::$_lang[$keys[0]][$keys[1]] ?? '';
        }
        return $replacer && $string ? \Helpers\Strings::parser($string, $replacer) : $string;
    }

    /**
     * 设置语言
     * @access public
     * @param array|string $key
     * @param string    $range  作用域
     * @param mixed        $value
     */
    public static function set($key, $range, $value = null) {
        if (is_array($key)) {
            $keys = array_change_key_case($key, CASE_LOWER);
            foreach ($keys as $key => $value) {
                self::$_lang[$range][$key] = $value;
            }
        } else {
            if (!empty($key)) {
                self::$_lang[$range][$key] = $value;
            }
        }
    }

    /**
     * 返回所有语言
     * @param string $key
     * @return mixed
     */
    public static function all($key = null) {
        return !$key ? self::$_lang : (self::$_lang[$key] ?? null);
    }

    /**
     * 解析配置文件或内容
     * @access public
     * @param string    $lang 配置文件路径或内容
     * @param string    $range  作用域
     * @param string    $type 语言类型
     */
    public static function parse($lang, $range, $type = null) {
        foreach ((array) $lang as $value) {
            if (empty($type)) {
                $type = File::extension($value);
            } else {
                $value .= '.' . $type;
            }
            $build = Loader::initialize('\\Drivers\\Lang\\' . ucwords($type));
            if ($build->supports($value)) {
                Log::setLog('lang', 'read:' . $value, function () use ($build, $value, $range) {
                    self::set($build->read($value), $range);
                });
            }
        }
    }

}
