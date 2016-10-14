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

namespace Interfaces;

/**
 * 加载类
 * @author CookPHP <admin@cookphp.org>
 *
 */
interface Config {

    /**
     *  加载配制
     * @param string $resource
     */
    public function read($resource);

    /**
     * 检测是否支持
     * @param string $resource
     */
    public function supports($resource): bool;
}
