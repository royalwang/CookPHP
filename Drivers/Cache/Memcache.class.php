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

namespace Drivers\Cache;

use \Interfaces\Cache;
use \Core\{
    Config,
    Error
};

/**
 * Memcache缓存驱动
 */
class Memcache implements Cache {

    private $config = [], $handler;

    /**
     * 架构函数
     * @access public
     */
    function __construct() {
        if (!extension_loaded('memcache')) {
            Error::show('Error Cache Handler:Memcache', 500);
        }
        $this->config['host'] = Config::get('cache.host') ?: '127.0.0.1';
        $this->config['port'] = Config::get('cache.port') ?: 11211;
        $this->config['timeout'] = Config::get('cache.timeout') ?: false;
        $this->config['persistent'] = Config::get('cache.persistent') ?: false;
        $this->config['prefix'] = Config::get('cache.prefix');
        $this->config['expire'] = Config::get('cache.expire');
        $func = $this->config['persistent'] ? 'pconnect' : 'connect';
        $this->handler = new \Memcache;
        $this->config['timeout'] === false ? $this->handler->$func($this->config['host'], $this->config['port']) : $this->handler->$func($this->config['host'], $this->config['port'], $this->config['timeout']);
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        return $this->handler->get($this->config['prefix'] . $name);
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
        return $this->handler->set($name, $value, 0, $expire) ? true : false;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function rm($name, $ttl = false) {
        $name = $this->config['prefix'] . $name;
        return $ttl === false ? $this->handler->delete($name) : $this->handler->delete($name, $ttl);
    }

    /**
     * 清除缓存
     * @access public
     * @return bool
     */
    public function clear() {
        return $this->handler->flush();
    }

}
