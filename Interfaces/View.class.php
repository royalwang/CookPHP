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
 * 模板引擎驱动
 * @author CookPHP <admin@cookphp.org>
 */
interface View {

    /**
     * 返回驱动
     */
    public function engine();

    /**
     * 呈现模板。
     * @param mixed $template 模板
     * @param mixed $data 赋值
     * @return string 
     */
    public function render($template, $data = null);

    /**
     * 检测是否支持
     * @param mixed  $template  模板
     * @param string $type 
     * @return bool
     */
    public function supports($template, $type = null);

    /**
     * 返回后缀
     * @return string
     */
    public function getSuffix();
}
