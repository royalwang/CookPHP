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

namespace Core;

/**
 * 公共类
 * @author CookPHP <admin@cookphp.org>
 */
abstract class Common {

    /**
     * 实例model
     * @access protected
     * @param string|null $table 表
     * @param array $config 配制
     * @return \Core\Model
     */
    protected function model($table = null, $config = []) {
        return Loader::model($table, $config);
    }

    /**
     * 返回引擎
     * @access protected
     * @param string $driver 驱动
     * @return \Core\Vi.ew
     */
    protected function view($driver = null) {
        return Loader::view($driver);
    }

    /**
     * 返回缓存
     * @access protected
     * @param string $driver 驱动
     * @return \Core\Cache
     */
    protected function cache($driver = null) {
        return Loader::cache($driver);
    }

    /**
     * 返回缓存用户定义查询
     * @access public
     * @param string $key 缓存变量名
     * @param \closure $callable 用户定义函数
     * @param int $expire 有效时间 0为永久
     * @return mixed
     */
    protected function cacheRemember($key, \closure $callable, $expire = 0) {
        return Loader::cacheRemember($key, $callable, $expire);
    }

    /**
     * 解析URL
     * @access protected
     * @param string $url
     * @param array $params
     * @return string
     */
    protected function url($url, $params = []) {
        return Loader::url($url, $params);
    }

    /**
     * 获取语言定义(不区分大小写)
     * @access protected
     * @param string|null   $name 语言变量
     * @param array         $vars 变量替换
     * @return mixed
     */
    protected function lang($name = null, $vars = []) {
        return Loader::lang($name, $vars);
    }

    /**
     * 获取配置参数 为空则获取所有配置
     * @access protected
     * @param string    $name 配置参数名（支持二级配置 .号分割）
     * @return mixed
     */
    protected function config($name = null) {
        return Loader::config($name);
    }

    /**
     * 实例Helpers
     * @access protected
     * @param string    $class
     * @param array        $vars   变量
     * @return mixed
     */
    protected function helpers($class, $vars = []) {
        return Loader::helpers($class, $vars);
    }

    /**
     * 实例Libraries
     * @access protected
     * @param string    $class
     * @param array        $vars   变量
     * @return mixed
     */
    protected function libraries($class, $vars = []) {
        return Loader::libraries($class, $vars);
    }

    /**
     * 实例Plugin
     * @access public
     * @param string    $class
     * @param array        $vars   变量
     * @return mixed
     */
    public static function plugin($class, $vars = []) {
        return Loader::plugin($class, $vars);
    }

}
