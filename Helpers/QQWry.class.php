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
 * @license <a href='http://www.cookphp.org'>CookPHP</a>
 */

namespace Helpers;

use \Core\Loader;

defined('__QQWRY__') or define('__QQWRY__', __COOK__ . 'Data' . DS . 'Ip' . DS . 'qqwry.dat');

/**
 * QQ纯真IP类
 * @author 费尔 <admin@xuai.cn>
 */
class QQWry {

    /**
     * 返回IP物理地址
     * @access public
     * @param string|null $ip IP
     * @param string $ipDat 纯真IP数据库文件地址
     * @access public
     * @return array region,address
     * Array
      (
      [ip] => 202.201.48.1
      [region] => 甘肃省兰州市
      [province] => 甘肃省
      [city] => 兰州市
      [county] =>
      [area] =>
      [address] => 西北师范大学
      )
     */
    public static function getInfo($ip = '') {
        static $_info = [];
        if (empty($ip)) {
            $ip = \Core\Request::ip();
        }
        if (!Validate::isIP4($ip)) {
            return 'n/a';
        }
        $_info[$ip] = Loader::initialize('\\Core\\Cache')->remember((string) $ip, function () use ($ip) {
            return self::convertip($ip);
        }, 86400);
        return $_info[$ip] ?? [];
    }

    private static function convertip($ip) {
        $ip1num = 0;
        $ip2num = 0;
        $ipAddr1 = "";
        $ipAddr2 = "";
        $datPath = __QQWRY__;
        if (!$fd = @fopen($datPath, 'rb')) {
            return 'n/a';
        }
        $_ip = explode('.', $ip);
        $ipNum = $_ip[0] * 16777216 + $_ip[1] * 65536 + $_ip[2] * 256 + $_ip[3];
        $DataBegin = fread($fd, 4);
        $DataEnd = fread($fd, 4);
        $ipbegin = implode('', unpack('L', $DataBegin));
        if ($ipbegin < 0) {
            $ipbegin += pow(2, 32);
        }
        $ipend = implode('', unpack('L', $DataEnd));
        if ($ipend < 0) {
            $ipend += pow(2, 32);
        }
        $ipAllNum = ($ipend - $ipbegin) / 7 + 1;
        $BeginNum = 0;
        $EndNum = $ipAllNum;
        while ($ip1num > $ipNum || $ip2num < $ipNum) {
            $Middle = intval(($EndNum + $BeginNum) / 2);
            fseek($fd, $ipbegin + 7 * $Middle);
            $ipData1 = fread($fd, 4);
            if (strlen($ipData1) < 4) {
                fclose($fd);
                return 'n/a';
            }
            $ip1num = implode('', unpack('L', $ipData1));
            if ($ip1num < 0) {
                $ip1num += pow(2, 32);
            }

            if ($ip1num > $ipNum) {
                $EndNum = $Middle;
                continue;
            }
            $DataSeek = fread($fd, 3);
            if (strlen($DataSeek) < 3) {
                fclose($fd);
                return 'n/a';
            }
            $DataSeek = implode('', unpack('L', $DataSeek . chr(0)));
            fseek($fd, $DataSeek);
            $ipData2 = fread($fd, 4);
            if (strlen($ipData2) < 4) {
                fclose($fd);
                return 'n/a';
            }
            $ip2num = implode('', unpack('L', $ipData2));
            if ($ip2num < 0) {
                $ip2num += pow(2, 32);
            }
            if ($ip2num < $ipNum) {
                if ($Middle == $BeginNum) {
                    fclose($fd);
                    return 'n/a';
                }
                $BeginNum = $Middle;
            }
        }
        $ipFlag = fread($fd, 1);
        if ($ipFlag == chr(1)) {
            $ipSeek = fread($fd, 3);
            if (strlen($ipSeek) < 3) {
                fclose($fd);
                return 'n/a';
            }
            $ipSeek = implode('', unpack('L', $ipSeek . chr(0)));
            fseek($fd, $ipSeek);
            $ipFlag = fread($fd, 1);
        }
        if ($ipFlag == chr(2)) {
            $AddrSeek = fread($fd, 3);
            if (strlen($AddrSeek) < 3) {
                fclose($fd);
                return 'n/a';
            }
            $ipFlag = fread($fd, 1);
            if ($ipFlag == chr(2)) {
                $AddrSeek2 = fread($fd, 3);
                if (strlen($AddrSeek2) < 3) {
                    fclose($fd);
                    return 'n/a';
                }
                $AddrSeek2 = implode('', unpack('L', $AddrSeek2 . chr(0)));
                fseek($fd, $AddrSeek2);
            } else {
                fseek($fd, -1, SEEK_CUR);
            }
            while (($char = fread($fd, 1)) != chr(0)) {
                $ipAddr2 .= $char;
            }
            $AddrSeek = implode('', unpack('L', $AddrSeek . chr(0)));
            fseek($fd, $AddrSeek);
            while (($char = fread($fd, 1)) != chr(0)) {
                $ipAddr1 .= $char;
            }
        } else {
            fseek($fd, -1, SEEK_CUR);
            while (($char = fread($fd, 1)) != chr(0)) {
                $ipAddr1 .= $char;
            }
            $ipFlag = fread($fd, 1);
            if ($ipFlag == chr(2)) {
                $AddrSeek2 = fread($fd, 3);
                if (strlen($AddrSeek2) < 3) {
                    fclose($fd);
                    return 'n/a';
                }
                $AddrSeek2 = implode('', unpack('L', $AddrSeek2 . chr(0)));
                fseek($fd, $AddrSeek2);
            } else {
                fseek($fd, -1, SEEK_CUR);
            }
            while (($char = fread($fd, 1)) != chr(0)) {
                $ipAddr2 .= $char;
            }
        }
        fclose($fd);
        if (preg_match('/http/i', $ipAddr2)) {
            $ipAddr2 = '';
        }
        $ipaddr = $ipAddr1 . ' ' . $ipAddr2;
        $ipaddr = Strings::autoCharset($ipaddr);
        $ipaddr = preg_replace('/CZ88.NET/is', '', $ipaddr);
        $ipaddr = preg_replace('/^s*/is', '', $ipaddr);
        $ipaddr = preg_replace('/s*$/is', '', $ipaddr);
        if (preg_match('/http/i', $ipaddr) || $ipaddr == '') {
            $ipaddr = 'n/a';
        }
        list($region, $address) = preg_split('/\s+/', $ipaddr);
        preg_match("/(.*省)?(.*市)?(.*县)?(.*区)?(.*?)/", $region, $match);
        $info = [];
        $info['ip'] = $ip;
        $info['region'] = $region;
        $info['province'] = $match[1] ?? '';
        $info['city'] = $match[2] ?? '';
        $info['county'] = $match[3] ?? '';
        $info['area'] = $match[4] ?? '';
        $info['address'] = $address;
        return $info;
    }

}
