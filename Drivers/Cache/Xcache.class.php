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

namespace Drivers\Cache;

use \Interfaces\Cache;
use \Core\{
    Config,
    Error
};

/**
 * Xcache缓存驱动
 */
class Xcache implements Cache {

    private $config = [];

    /**
     * 架构函数
     * @access public
     */
    public function __construct() {
        if (!function_exists('xcache_info')) {
            Error::show('Error Cache Handler:Xcache', 500);
        }
        $this->config['prefix'] = Config::get('cache.prefix');
        $this->config['expire'] = Config::get('cache.expire');
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        $name = $this->config['prefix'] . $name;
        if (xcache_isset($name)) {
            return xcache_get($name);
        }
        return false;
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return bool
     */
    public function set($name, $value, $expire = null) {
        if (is_null($expire)) {
            $expire = $this->config['expire'];
        }
        $name = $this->config['prefix'] . $name;
        return xcache_set($name, $value, $expire) ? true : false;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function rm($name) {
        return xcache_unset($this->config['prefix'] . $name);
    }

    /**
     * 清除缓存
     * @access public
     * @return bool
     */
    public function clear() {
        return xcache_clear_cache(XC_TYPE_VAR, 0);
    }

}
