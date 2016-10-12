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

namespace Core;

/**
 * 加载类
 * @author CookPHP <admin@cookphp.org>
 *
 */
class Loader {

    /**
     * 注册自动加载机
     * @access public
     * @param string $function
     * @param bool $prepend
     * @return bool
     */
    public static function register($function = null, $prepend = true) {
        spl_autoload_register($function ?: '\\Core\\Loader::loadClass', true, (bool) $prepend);
    }

    /**
     * 注销自动加载机
     * @access public
     * @return null
     */
    public static function unregister($function = null) {
        spl_autoload_unregister($function ?: '\\Core\\Loader::loadClass');
    }

    /**
     * 加载类
     * @access public
     * @param string $class
     * @return bool
     */
    public static function loadClass($class) {
        $init = explode('\\', $class, 2);
        if (in_array($init[0], ['Core', 'Drivers', 'Helpers', 'Interfaces'])) {
            if (self::requireFile(__COOK__ . rtrim($init[0] . DS . str_replace('\\', DS, $init[1] ?? null), DS) . '.class.php')) {
                return true;
            }
        }
        if (in_array($init[0], ['Controller', 'Model', 'Libraries', 'Plugin', 'View'])) {
            if (self::requireFile(__APP__ . Route::getProject() . DS . rtrim($init[0] . DS . str_replace('\\', DS, $init[1] ?? null), DS) . '.class.php') || self::requireFile(__COMMON__ . rtrim($init[0] . DS . str_replace('\\', DS, $init[1] ?? null), DS) . '.class.php')) {
                return true;
            }
        }
        return self::requireFile(__APP__ . rtrim($init[0] . DS . str_replace('\\', DS, $init[1] ?? null), DS) . '.class.php') || self::requireFile(__COMMON__ . rtrim($init[0] . DS . str_replace('\\', DS, $init[1] ?? null), DS) . '.class.php') || self::requireFile(__COOK__ . rtrim($init[0] . DS . str_replace('\\', DS, $init[1] ?? null), DS) . '.class.php');
    }

    /**
     * 调用反射执行类的方法 支持参数绑定
     * @access public
     * @param string|array $method 方法
     * @param array        $vars   变量
     * @return mixed
     */
    public static function initialize($method, $vars = []) {
        return self::invokeMethod((array) $method, $vars);
    }

    /**
     * 调用反射执行类的方法 支持参数绑定
     * @access public
     * @param string|array $method 方法
     * @param array        $vars   变量
     * @return mixed
     */
    public static function invokeMethod($method, $vars = []) {
        static $_class = [];
        if (is_array($method) && class_exists($method[0])) {
            $class = $_class[$method[0]] ?? $_class[$method[0]] = new $method[0];
            if (!empty($method[1])) {
                $reflect = new \ReflectionMethod($class, $method[1]);
            } else {
                return $class;
            }
        } else {
            if (!class_exists($method[0], false)) {
                Error::show("Unable to load class: $method[0]");
            }
        }
        if ($reflect->getNumberOfParameters() > 0) {
            empty($vars) && ($vars = Input::param());
            $args = [];
            foreach ($reflect->getParameters() as $key => $param) {
                $name = $param->getName();
                $args[] = $vars[$name] ?? ($vars[$key] ?? ($param->isDefaultValueAvailable() ? $param->getDefaultValue() : null));
            }
            return $reflect->invokeArgs($class, $args);
        } else {
            return $reflect->invoke($class);
        }
    }

    /**
     * 获取返回文件
     * @access public
     * @param string $file
     * @return mixed
     */
    public static function loadFile($file) {
        return file_exists($file) ? require $file : null;
    }

    /**
     * 唯一包含并运行指定文件
     * @access public
     * @param string $file
     * @return mixed
     */
    public static function requireOnce($file) {
        if (file_exists($file)) {
            require_once $file;
        }
    }

    /**
     * 加载文件
     * @param string $file
     * @return bool 
     */
    public static function requireFile($file) {
        if (file_exists($file)) {
            require $file;
            return true;
        }
        return false;
    }

    /**
     * URL重定向
     * @access public
     * @param string $url 跳转的URL表达式
     * @param array $params 其它URL参数
     * @param string $msg 跳转提示信息
     */
    public static function redirect($url) {
        exit(header('Location:' . $url));
    }

    /**
     * 实例model
     * @access public
     * @param string|null $table 表
     * @param array $config 配制
     * @return \Core\Model
     */
    public static function model($table = null, $config = []) {
        static $_model = [];
        return $_model[$table] ?? ($_model[$table] = (!empty($table) && class_exists(($newtable = '\\Model\\' . Route::parseName($table, true))) ? self::initialize($newtable) : new Model($table ?: null, $config)));
    }

    /**
     * 返回引擎
     * @access public
     * @param string $driver 驱动
     * @return \Core\Vi.ew
     */
    public static function view($driver = null) {
        static $_view = [];
        return $_view[$driver] ?? ($_view[$driver] = self::initialize(false !== strpos(($_driver = (ucwords($driver) ?: ( ucwords(Config::get('view.driver')) ?: '\\Core\\Engine'))), '\\') ? $_driver : '\\Drivers\\View\\' . $_driver));
    }

    /**
     * 返回缓存
     * @access public
     * @param string $driver 驱动
     * @return \Core\Cache
     */
    public static function cache($driver = null) {
        static $_cache = [];
        return $_cache[$driver] ?? ($_cache[$driver] = new Cache($driver));
    }

    /**
     * 返回缓存用户定义查询
     * @access public
     * @param string $key 缓存变量名
     * @param \closure $callable 用户定义函数
     * @param int $expire 有效时间 0为永久
     * @return mixed
     */
    public static function cacheRemember($key, \closure $callable, $expire = 0) {
        return self::cache()->remember($key, $callable, $expire);
    }

    /**
     * 解析URL
     * @access public
     * @param string $url
     * @param array $params
     * @return string
     */
    public static function url($url, $params = []) {
        return Url::parse($url, $params);
    }

    /**
     * 获取语言定义(不区分大小写)
     * @access public
     * @param string|null   $name 语言变量
     * @param array         $vars 变量替换
     * @return mixed
     */
    public static function lang($name = null, $vars = []) {
        return Lang::get($name, $vars);
    }

    /**
     * 获取配置参数 为空则获取所有配置
     * @access public
     * @param string    $name 配置参数名（支持二级配置 .号分割）
     * @return mixed
     */
    public static function config($name = null) {
        return Config::get($name);
    }

    /**
     * 实例Helpers
     * @access public
     * @param string    $class
     * @param array        $vars   变量
     * @return mixed
     */
    public static function helpers($class, $vars = []) {
        return self::initialize('\\Helpers\\' . $class, $vars);
    }

    /**
     * 实例Libraries
     * @access public
     * @param string    $class
     * @param array        $vars   变量
     * @return mixed
     */
    public static function libraries($class, $vars = []) {
        return self::initialize('\\Libraries\\' . $class, $vars);
    }

    /**
     * 实例Plugin
     * @access public
     * @param string    $class
     * @param array        $vars   变量
     * @return mixed
     */
    public static function plugin($class, $vars = []) {
        return self::initialize('\\Plugin\\' . $class, $vars);
    }

}
