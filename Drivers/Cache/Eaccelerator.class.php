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
 * Eaccelerator缓存驱动
 */
class Eaccelerator implements Cache {

    private $config = [];

    /**
     * 架构函数
     * @access public
     */
    public function __construct() {
        if (!function_exists('eaccelerator_get')) {
            Error::show('Error Cache Handler:Eaccelerator', 500);
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
        return eaccelerator_get($this->config['prefix'] . $name);
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
        eaccelerator_lock($name);
        return eaccelerator_put($name, $value, $expire) ? true : false;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function rm($name) {
        return eaccelerator_rm($this->config['prefix'] . $name);
    }

    public function clear() {
        return eaccelerator_clear();
    }

}
