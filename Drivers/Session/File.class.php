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

namespace Drivers\Session;

use \Core\Config;
use Helpers\File as F;
use \Interfaces\Session;

class File implements Session {

    private $_config;

    /**
     * 打开Session
     * @access public
     * @param string $savePath
     * @param mixed  $sessName
     * @return bool
     * @throws Exception
     */
    public function open($savePath, $sessName) {
        $this->_config['path'] = rtrim(Config::get('session.path') ?: __TMP__, '\\/') . DS . 'Session' . DS;
        $this->_config['prefix'] = Config::get('session.prefix');
        if (Config::get('session.ip')) {
            $this->_config['prefix'] .= \Core\Request::ip();
        }
        return true;
    }

    /**
     * 关闭Session
     * @access public
     */
    public function close() {

        return true;
    }

    /**
     * 读取Session
     * @access public
     * @param string $sessID
     * @return bool|string
     */
    public function read($sessID) {
        return F::get($this->filename($sessID));
    }

    /**
     * 写入Session
     * @access public
     * @param string $sessID
     * @param String $sessData
     * @return bool
     */
    public function write($sessID, $sessData) {
        F::put($this->filename($sessID), $sessData);
        return true;
    }

    /**
     * 删除Session
     * @access public
     * @param string $sessID
     * @return bool|void
     */
    public function destroy($sessID) {
        F::remove($this->filename($sessID));
        return true;
    }

    /**
     * Session 垃圾回收
     * @access public
     * @param string $sessMaxLifeTime
     * @return bool
     */
    public function gc($sessMaxLifeTime) {
        return true;
    }

    /**
     * 取得变量的存储文件名
     *
     * @access private
     * @param string $name 缓存变量名
     * @return string
     */
    private function filename($name) {
        $name = md5($this->_config['prefix'] . $name);
        $dir = '';
        for ($i = 0; $i < 6; $i ++) {
            $dir .= $name{$i} . DS;
        }
        $filename = $dir . $name . '.session';
        return $this->_config['path'] . $filename;
    }

}
