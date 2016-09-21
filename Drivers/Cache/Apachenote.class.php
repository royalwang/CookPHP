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
 * Apachenote缓存驱动
 */
class Apachenote implements Cache {

    private $config = [], $handler;

    /**
     * 架构函数
     * @access public
     */
    public function __construct() {
        $this->config['host'] = Config::get('cache.host') ?: '127.0.0.1';
        $this->config['port'] = Config::get('cache.port') ?: 1042;
        $this->config['timeout'] = Config::get('cache.timeout') ?: 10;
        $this->config['prefix'] = Config::get('cache.prefix');
        $this->config['expire'] = Config::get('cache.expire');
        $this->handler = null;
        $this->open();
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        $this->open();
        $name = $this->config['prefix'] . $name;
        $s = 'F' . pack('N', strlen($name)) . $name;
        fwrite($this->handler, $s);

        for ($data = ''; !feof($this->handler);) {
            $data .= fread($this->handler, 4096);
        }
        $this->close();
        return $data === '' ? '' : unserialize($data);
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @return bool
     */
    public function set($name, $value) {
        $this->open();
        $value = serialize($value);
        $name = $this->config['prefix'] . $name;
        $s = 'S' . pack('NN', strlen($name), strlen($value)) . $name . $value;
        fwrite($this->handler, $s);
        $ret = fgets($this->handler);
        $this->close();
        if ($ret === "OK\n") {
            return true;
        }
        return false;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function rm($name) {
        $this->open();
        $name = $this->config['prefix'] . $name;
        $s = 'D' . pack('N', strlen($name)) . $name;
        fwrite($this->handler, $s);
        $ret = fgets($this->handler);
        $this->close();
        return $ret === "OK\n";
    }

    /**
     * 关闭缓存
     * @access private
     */
    private function close() {
        fclose($this->handler);
        $this->handler = false;
    }

    /**
     * 打开缓存
     * @access private
     */
    private function open() {
        if (!is_resource($this->handler)) {
            $this->handler = fsockopen($this->config['host'], $this->config['port'], $_, $_, $this->config['timeout']);
        }
    }

    public function clear(): bool {
        
    }

}
