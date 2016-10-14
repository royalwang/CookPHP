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
 * Sqlite缓存驱动
 */
class Sqlite implements Cache {

    private $config = [], $handler;

    /**
     * 架构函数
     * @access public
     */
    public function __construct() {
        if (!extension_loaded('sqlite')) {
            Error::show('Error Cache Handler:sqlite', 500);
        }
        $this->config['prefix'] = Config::get('cache.prefix');
        $this->config['expire'] = Config::get('cache.expire');
        $this->config['persistent'] = Config::get('cache.persistent') ?: false;
        $this->config['db'] = Config::get('cache.db') ?: ':memory:';
        $this->config['table'] = Config::get('cache.table') ?: 'sharedmemory';
        $func = $this->config['persistent'] ? 'sqlite_popen' : 'sqlite_open';
        $this->handler = $func($this->config['db']);
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        $name = $this->config['prefix'] . $this->escape($name);
        $sql = 'SELECT value FROM ' . $this->config['table'] . ' WHERE var=\'' . $name . '\' AND (expire=0 OR expire >' . time() . ') LIMIT 1';
        $result = sqlite_query($this->handler, $sql);
        if (sqlite_num_rows($result)) {
            $content = sqlite_fetch_single($result);
            if (Config::get('cache.compress') && function_exists('gzcompress')) {
                $content = gzuncompress($content);
            }
            return unserialize($content);
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
        $name = $this->config['prefix'] . $this->escape($name);
        $value = $this->escape(serialize($value));
        if (is_null($expire)) {
            $expire = $this->config['expire'];
        }
        $expire = ($expire == 0) ? 0 : (time() + $expire);
        if (Config::get('cache.compress') && function_exists('gzcompress')) {
            $value = gzcompress($value, 3);
        }
        $sql = 'REPLACE INTO ' . $this->config['table'] . ' (var, value,expire) VALUES (\'' . $name . '\', \'' . $value . '\', \'' . $expire . '\')';
        return sqlite_query($this->handler, $sql) ? true : false;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function rm($name) {
        $name = $this->config['prefix'] . $this->escape($name);
        $sql = 'DELETE FROM ' . $this->config['table'] . ' WHERE var=\'' . $name . '\'';
        sqlite_query($this->handler, $sql);
        return true;
    }

    /**
     * 清除缓存
     * @access public
     * @return bool
     */
    public function clear() {
        $sql = 'DELETE FROM ' . $this->config['table'];
        sqlite_query($this->handler, $sql);
        return;
    }

    private function escape($str) {
        if ($str == '') {
            return '';
        }
        if (function_exists('sqlite_escape_string')) {
            $str = sqlite_escape_string(trim($str));
        } else {
            $str = addslashes(trim($str));
        }
        return $str;
    }

}
