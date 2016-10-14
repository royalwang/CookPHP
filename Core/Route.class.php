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
 * 路由操作
 * @author CookPHP <admin@cookphp.org>
 */
class Route {

    static $_controller, $_action, $_project;

    /**
     * 初始路由器
     * @access private
     */
    public static function init() {
        self::initController();
        self::initSession();
    }

    /**
     * 执行控制器
     * @access private
     */
    public static function run() {
        Loader::initialize(['\\Controller\\' . self::getController(), self::getAction()]);
    }

    /**
     * 返回项目名称
     * @access string
     * @return string
     */
    public static function getProject() {
        return self::$_project;
    }

    /**
     * 返回控制器名称
     * @access string
     * @return string
     */
    public static function getController() {
        return self::$_controller;
    }

    /**
     * 返回动作名称
     * @access string
     * @return string
     */
    public static function getAction() {
        return self::$_action;
    }

    /**
     * URL路由检测（PATH_INFO)
     * @access private
     */
    private static function initController() {
        $array = Url::explode(Request::path());
        self::$_project = Config::get('route.domain') ? ucfirst(strtolower(preg_match('/^[A-Za-z](\/|\.|\w)*$/', ($host = strstr(Request::host(), '.', true))) ? ($host !== 'www' ? $host : Config::get('route.project')) : Config::get('route.project'))) : ucfirst(strtolower(strip_tags(!empty(($project = array_shift($array))) ? $project : Config::get('route.project'))));
        if (is_dir(__APP__ . self::$_project)) {
            self::$_controller = ucfirst(strtolower(strip_tags(!empty(($controller = array_shift($array))) ? $controller : Config::get('route.controller'))));
        } else {
            self::$_project = ucfirst(strtolower(Config::get('route.project')));
            self::$_controller = ucfirst(strtolower($project));
        }
        self::$_action = strtolower(strip_tags(!empty(($action = array_shift($array))) ? $action : Config::get('route.action')));
        define('APP_PROJECT', self::getProject());
        define('APP_CONTROLLER', self::getController());
        define('APP_ACTION', self::getAction());
        if (!empty($array)) {
            self::parseVar($array);
        }
        (!preg_match('/^[A-Za-z](\/|\.|\w)*$/', self::$_project) || !preg_match('/^[A-Za-z](\/|\.|\w)*$/', self::$_controller) || !preg_match('/^[A-Za-z](\w)*$/', self::$_action)) && Error::show('The URI you submitted has disallowed characters.', 400);
    }

    /**
     * 初始Session
     * @access private
     */
    private static function initSession() {
        Config::get('session.start') && Session::init();
    }

    /**
     * 解释 Var
     * @access private
     */
    private static function parseVar($url) {
        if (!empty($url)) {
            preg_replace_callback('/(\w+)\/([^\/]+)/', function ($match) use(&$var) {
                $var[strtolower($match[1])] = strip_tags($match[2]);
            }, implode('/', $url));
            if (!empty($var)) {
                $_GET = array_merge($var, $_GET);
            }
        }
    }

    /**
     * 字符串命名风格转换
     * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
     * @param string  $name 字符串
     * @param integer $type 转换类型
     * @return string
     */
    public static function parseName($name, $type = 0) {
        return $type ? ucfirst(preg_replace_callback('/_([a-zA-Z])/', function ($match) {
                            return strtoupper($match[1]);
                        }, $name)) : strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }

}
