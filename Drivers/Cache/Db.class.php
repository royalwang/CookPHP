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
use \Core\Config;

/**
 * 数据库方式缓存驱动
 *    CREATE TABLE think_cache (
 *      cachekey varchar(255) NOT null,
 *      expire int(11) NOT null,
 *      data blob,
 *      datacrc int(32),
 *      UNIQUE KEY `cachekey` (`cachekey`)
 *    );
 */
class Db implements Cache {

    private $config = [], $handler;

    /**
     * 架构函数
     * @access public
     */
    public function __construct() {
        if (empty($config)) {
            $config = array(
                'table' => C('DATA_CACHE_TABLE'),
            );
        }
        $this->config['prefix'] = Config::get('cache.prefix');
        $this->config['expire'] = Config::get('cache.expire');
        $this->handler = \Think\Db::getInstance();
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        $name = $this->config['prefix'] . addslashes($name);
        $result = $this->handler->query('SELECT `data`,`datacrc` FROM `' . $this->config['table'] . '` WHERE `cachekey`=\'' . $name . '\' AND (`expire` =0 OR `expire`>' . time() . ') LIMIT 0,1');
        if (false !== $result) {
            $result = $result[0];
            if (Config::get('cache.check')) {//开启数据校验
                if ($result['datacrc'] != md5($result['data'])) {//校验错误
                    return false;
                }
            }
            $content = $result['data'];
            if (C('DATA_CACHE_COMPRESS') && function_exists('gzcompress')) {
                //启用数据压缩
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
     * @param integer $expire  有效时间（秒）
     * @return bool
     */
    public function set($name, $value, $expire = null) {
        $data = serialize($value);
        $name = $this->config['prefix'] . addslashes($name);
        if (C('DATA_CACHE_COMPRESS') && function_exists('gzcompress')) {
            //数据压缩
            $data = gzcompress($data, 3);
        }
        if (Config::get('cache.check')) {//开启数据校验
            $crc = md5($data);
        } else {
            $crc = '';
        }
        if (is_null($expire)) {
            $expire = $this->config['expire'];
        }
        $expire = ($expire == 0) ? 0 : (time() + $expire); //缓存有效期为0表示永久缓存
        $result = $this->handler->query('select `cachekey` from `' . $this->config['table'] . '` where `cachekey`=\'' . $name . '\' limit 0,1');
        if (!empty($result)) {
            //更新记录
            $result = $this->handler->execute('UPDATE ' . $this->config['table'] . ' SET data=\'' . $data . '\' ,datacrc=\'' . $crc . '\',expire=' . $expire . ' WHERE `cachekey`=\'' . $name . '\'');
        } else {
            //新增记录
            $result = $this->handler->execute('INSERT INTO ' . $this->config['table'] . ' (`cachekey`,`data`,`datacrc`,`expire`) VALUES (\'' . $name . '\',\'' . $data . '\',\'' . $crc . '\',' . $expire . ')');
        }
        if ($result) {
            if ($this->config['length'] > 0) {
                // 记录缓存队列
                $this->queue($name);
            }
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
        $name = $this->config['prefix'] . addslashes($name);
        return $this->handler->execute('DELETE FROM `' . $this->config['table'] . '` WHERE `cachekey`=\'' . $name . '\'');
    }

    /**
     * 清除缓存
     * @access public
     * @return bool
     */
    public function clear() {
        return $this->handler->execute('TRUNCATE TABLE `' . $this->config['table'] . '`');
    }

}
