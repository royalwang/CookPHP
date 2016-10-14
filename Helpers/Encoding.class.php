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
 * @license <a href='http://www.cookphp.org'>CookPHP</a>
 */

namespace Helpers;

/**
 * 编码类
 * @author 费尔 <admin@cookphp.org>
 */
class Encoding {

    /**
     * 返回汉字返回首字母
     * @param string $str 汉字
     * @return array
     */
    public static function findPinyin(string $str): array {
        return self::pinyin($str, true);
    }

    /**
     * 返回汉字拼音
     * @staticvar array $pinyins
     * @param string $str 汉字
     * @param bool $ishead 是否返回首字母
     * @return array
     */
    public static function pinyin(string $str, bool $ishead = false): array {
        static $pinyins = [];
        defined('__PINYIN__') or define('__PINYIN__', __COOK__ . 'Data' . DS . 'Encoding' . DS . 'pinyin.dat');
        $restr = [];
        $str = self::autoCharset(trim($str), 'utf8', 'gbk');
        $slen = strlen($str);
        if ($slen < 2) {
            return $str;
        }
        if (empty($pinyins)) {
            $fp = fopen(__PINYIN__, 'r');
            while (!feof($fp)) {
                $line = trim(fgets($fp));
                $pinyins[$line[0] . $line[1]] = substr($line, 3, strlen($line) - 3);
            }
            fclose($fp);
        }
        for ($i = 0; $i < $slen; $i++) {
            if (ord($str[$i]) > 0x80) {
                $c = $str[$i] . $str[$i + 1];
                $i++;
                if (isset($pinyins[$c])) {
                    if (!$ishead) {
                        $restr[] = $pinyins[$c];
                    } else {
                        $restr[] = $pinyins[$c][0];
                    }
                }
            } elseif (preg_match("/[a-z0-9]/i", $str[$i])) {
                $restr[] = $str[$i];
            }
        }
        return $restr;
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
     * @param string|array $string 字符
     * @param string $from 当前编码
     * @param string $to 目标编码
     */
    static public function autoCharset($string, $from = 'gbk', $to = CHARSET) {
        return Strings::autoCharset($string, $from, $to);
    }

}
