<?php

/**
 * CookPHP framework
 *
 * @name CookPHP framework
 * @package CookPHP
 * @author CookPHP <admin@cookphp.org>
 * @version 1.0 Beta
 * @link http://www.cookphp.org
 * @copyright cookphp.org
 * @license <a href="http://www.cookphp.org">CookPHP</a>
 */

namespace Helpers;

/**
 * 字符处理类
 * @author CookPHP <admin@cookphp.org>
 */
class Strings {

    /**
     * PHP替换标签字符
     * @access public
     * @param string $string 内容
     * @param string $replacer 替换标签
     * @return string
     */
    public static function parser($string, $replacer): string {
        return str_replace(array_keys($replacer), array_values($replacer), $string);
    }

    /**
     * 检查字符串是否是UTF8编码
     *
     * @param string $string 字符串
     * @return Boolean
     */
    static public function isUtf8($string): bool {
        return Validate::isUtf8($string);
    }

    /**
     * 自动转换字符集 支持数组转换
     * 
     * @param string $string
     * @param string $from
     * @param string $to
     */
    static public function autoCharset($string, $from = 'gbk', $to = CHARSET) {
        if (strtolower($to) == 'utf8') {
            $to = CHARSET;
        }
        if (strtolower($from) === strtolower($to) || empty($string) || (is_scalar($string) && !is_string($string))) {
            return $string;
        }
        if (is_string($string)) {
            if (function_exists('mb_convert_encoding')) {
                return mb_convert_encoding($string, $to, $from);
            } elseif (function_exists('iconv')) {
                return iconv($from, $to, $string);
            } else {
                return $string;
            }
        } elseif (is_array($string)) {
            foreach ($string as $key => $val) {
                $_key = self::autoCharset($key, $from, $to);
                $string[$_key] = self::autoCharset($val, $from, $to);
                if ($key != $_key) {
                    unset($string[$key]);
                }
            }
            return $string;
        } else {
            return $string;
        }
    }

    /**
     * 字符串截取，支持中文和其他编码
     * @static
     * @access public
     * @param string $str 需要转换的字符串
     * @param string $start 开始位置
     * @param string $length 截取长度
     * @param string $charset 编码格式
     * @param string $suffix 截断显示字符
     * @return string
     */
    static public function msubstr($str, $start, $length, $charset = "utf-8", $suffix = true) {
        if (function_exists("mb_substr")) {
            $slice = mb_substr($str, $start, $length, $charset);
        } elseif (function_exists('iconv_substr')) {
            $slice = iconv_substr($str, $start, $length, $charset);
        } else {
            $re[CHARSET] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice = implode('', array_slice($match[0], $start, $length));
        }
        return $suffix && $slice != $str ? $slice . '...' : $slice;
    }

}
