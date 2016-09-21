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
 * 语言类
 * @author CookPHP <admin@cookphp.org>
 *
 */
interface Lang {

    /**
     *  加载语言
     * @param string $resource
     */
    public function read($resource);

    /**
     * 检测是否支持
     * @param string $resource
     */
    public function supports($resource): bool;
}
