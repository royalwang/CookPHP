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
use \Helpers\File as F;
use \Core\Config;

/**
 * 文件类型缓存类
 */
class File implements Cache {

    private $config = [];

    /**
     * 架构函数
     * @access public
     */
    public function __construct() {
        $this->config['path'] = rtrim(Config::get('cache.path') ?: __CACHE__, '\\/') . DS . 'Cache' . DS;
        $this->config['prefix'] = Config::get('cache.prefix');
        $this->config['expire'] = Config::get('cache.expire');
    }

    /**
     * 取得变量的存储文件名
     * @access private
     * @param string $name 缓存变量名
     * @return string
     */
    private function filename($name) {
        $name = md5($name);
        $dir = '';
        for ($i = 0; $i < 6; $i++) {
            $dir .= $name{$i} . DS;
        }
        return $this->config['path'] . $dir . $this->config['prefix'] . $name . '.php';
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        $filename = $this->filename($name);
        $content = F::get($filename);
        if ($content) {
            $expire = (int) substr($content, 8, 12);
            if ($expire != 0 && time() > filemtime($filename) + $expire) {
                F::delete($filename);
                return false;
            }
            if (Config::get('cache.check')) {
                $check = substr($content, 20, 32);
                $content = substr($content, 52, -3);
                if ($check != md5($content)) {
                    return false;
                }
            } else {
                $content = substr($content, 20, -3);
            }
            if (Config::get('cache.compress') && function_exists('gzcompress')) {
                $content = gzuncompress($content);
            }
            $content = unserialize($content);
            return $content;
        } else {
            return false;
        }
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param int $expire  有效时间 0为永久
     * @return bool
     */
    public function set($name, $value, $expire = null) {
        if (is_null($expire)) {
            $expire = $this->config['expire'];
        }
        $filename = $this->filename($name);
        $data = serialize($value);
        if (Config::get('cache.compress') && function_exists('gzcompress')) {
            $data = gzcompress($data, 3);
        }
        if (Config::get('cache.check')) {
            $check = md5($data);
        } else {
            $check = '';
        }
        $data = "<?php\n//" . sprintf('%012d', $expire) . $check . $data . "\n?>";
        $result = F::set($filename, $data);
        if ($result) {
            clearstatcache();
            return true;
        } else {
            return false;
        }
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function rm($name) {
        return F::delete($this->filename($name));
    }

    /**
     * 清除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function clear() {
        return F::deleteDirectory($this->config['path']);
    }

}
