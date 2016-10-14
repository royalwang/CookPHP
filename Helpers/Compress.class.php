<?php

/**
 * CookPHP framework
 *
 * @name CookPHP framework
 * @package CookPHP
 * @author CookPHP <admin@cookphp.org>
 * @version 0.0.1 Beta
 * @link http://www.cookphp.org
 * @copyright cookphp.org
 * @license <a href="http://www.cookphp.org">CookPHP</a>
 */

namespace Helpers;

/**
 * 压缩处理类
 * @author CookPHP <admin@cookphp.org>
 */
class Compress {

    /**
     * 压缩css
     * @access public
     * @param string $content
     * @return string
     */
    public static function css(string &$content): string {
        Format::css($content);
        return $content;
    }

    /**
     * 压缩HTML
     * @access public
     * @param string $content
     * @return string
     */
    public static function html(string &$content): string {
        Format::html($content);
        return $content;
    }

    /**
     * Gzip数据压缩传输 如果客户端支持
     * @access public
     * @param type $content
     * @param string $level 压缩的级别。可作为0为无压缩多达9为最大压缩
     * @link http://php.net/manual/zh/function.gzencode.php 参考
     * @return string
     */
    public static function obGzip(string &$content, $level = 9): string {
        if (!headers_sent() && extension_loaded("zlib") && strstr($_SERVER["HTTP_ACCEPT_ENCODING"], "gzip")) {
            self::gzip($content, $level);
            header('Content-Encoding:gzip');
            header('Vary:Accept-Encoding');
        }
        return $content;
    }

    /**
     * Gzip数据压缩传输 如果客户端支持
     * @access public
     * @param type $content
     * @param string $level 压缩的级别。可作为0为无压缩多达9为最大压缩
     * @link http://php.net/manual/zh/function.gzencode.php 参考
     * @return string
     */
    public static function gzip(string &$content, $level = 9): string {
        if (extension_loaded("zlib")) {
            $content = gzencode($content, $level);
        }
        return $content;
    }

}
