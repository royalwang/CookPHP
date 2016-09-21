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
 * Redis缓存驱动 
 */
class Redis implements Cache {

    private $config = [], $handler;

    /**
     * 架构函数
     * @access public
     */
    public function __construct() {
        if (!extension_loaded('redis')) {
            Error::show('Error Cache Handler:Redis', 500);
        }
        $this->config['host'] = Config::get('cache.host') ?: '127.0.0.1';
        $this->config['port'] = Config::get('cache.port') ?: 6379;
        $this->config['timeout'] = Config::get('cache.timeout') ?: false;
        $this->config['persistent'] = Config::get('cache.persistent') ?: false;
        $this->config['prefix'] = Config::get('cache.prefix');
        $this->config['expire'] = Config::get('cache.expire');
        $func = $this->config['persistent'] ? 'pconnect' : 'connect';
        $this->handler = new \Redis;
        $this->config['timeout'] === false ? $this->handler->$func($this->config['host'], $this->config['port']) : $this->handler->$func($this->config['host'], $this->config['port'], $this->config['timeout']);
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        $value = $this->handler->get($this->config['prefix'] . $name);
        $jsonData = json_decode($value, true);
        return ($jsonData === null) ? $value : $jsonData;
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
        $value = (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        if (is_int($expire) && $expire) {
            $result = $this->handler->setex($name, $expire, $value);
        } else {
            $result = $this->handler->set($name, $value);
        }
        return $result ? true : false;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function rm($name) {
        return $this->handler->delete($this->config['prefix'] . $name);
    }

    /**
     * 清除缓存
     * @access public
     * @return bool
     */
    public function clear() {
        return $this->handler->flushDB();
    }

}
