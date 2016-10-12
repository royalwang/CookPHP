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

namespace Interfaces;

/**
 * 插件管理
 * @author CookPHP <admin@cookphp.org>
 */
interface Plugin {

    /**
     * 安装
     * @return bool 
     */
    public function install();

    /**
     * 卸载
     * @return bool 
     */
    public function uninstall($force = false);

    /**
     * 升级
     * @return bool 
     */
    public function upgrade();
}
