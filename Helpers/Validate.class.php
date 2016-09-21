<?php

/**
 * SmartPHP framework
 *
 * @name SmartPHP framework
 * @package SmartPHP
 * @author 费尔 <admin@smartphp.cn>
 * @version 1.0 Beta
 * @link http://www.smartphp.cn
 * @copyright smartphp.cn
 * @license <a href="http://www.smartphp.cn">smartphp</a>
 */

namespace Helpers;

/**
 * 验证
 * @author 费尔 <admin@smartphp.cn>
 */
class Validate {

    /**
     * 验证是否是字母
     * @access public
     * @param string $string
     * @return bool
     */
    public static function isAlpha($string): bool {
        return (bool) preg_match('/^[A-Za-z]+$/', $string);
    }

    /**
     * 验证是否是字母和数字
     * @access public
     * @param string $string
     * @return bool
     */
    public static function isAlphaNum($string): bool {
        return (bool) preg_match('/^[A-Za-z0-9]+$/', $string);
    }

    /**
     * 验证是否是字母、数字和下划线 破折号
     * @access public
     * @param string $string
     * @return bool
     */
    public static function isAlphaDash($string): bool {
        return (bool) preg_match('/^[A-Za-z0-9\-\_]+$/', $string);
    }

    /**
     * 验证是否是有效网址
     *
     * @param string $host
     * @return bool
     */
//    public static function isActiveUrl($host) {
//        return checkdnsrr($host);
//    }

    /**
     * 验证是否是有合法的email
     * @access public
     * @param string $email
     * @return bool
     */
    public static function isEmail($email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * 验证是否是有效邮箱
     *
     * @param string $email
     * @return bool
     */
//    public static function isActiveEmail($email) {
//        return checkdnsrr($email);
//    }

    /**
     * 验证是否是有合法的IP
     * @access public
     * @param string $ip
     * @return bool
     */
    public static function isIP($ip): bool {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6);
    }

    /**
     * 验证是否是有合法的IP4
     * @access public
     * @param string $ip
     * @return bool
     */
    public static function isIP4($ip): bool {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    /**
     * 验证是否是有合法的IP6
     * @access public
     * @param string $ip
     * @return bool
     */
    public static function isIP6($ip): bool {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }

    /**
     * 验证是否为浮点数
     * @access public
     * @param string $float
     * @return bool
     */
    public static function isFloat($float): bool {
        return filter_var($float, FILTER_VALIDATE_FLOAT);
    }

    /**
     * 验证是否为整数
     * @access public
     * @param string $number
     * @return bool
     */
    public static function isNumber($number): bool {
        return filter_var($number, FILTER_VALIDATE_INT);
    }

    /**
     * 验证是否为整数
     * @access public
     * @param string $number
     * @return bool
     */
    public static function isInteger($number): bool {
        return self::isNumber($number);
    }

    /**
     * 验证是否为布尔值
     * @access public
     * @param string $bool
     * @return bool
     */
    public static function isBoolean($bool): bool {
        return filter_var($bool, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * 验证是否是中文
     * @access public
     * @param string $string 待验证的字串
     * @return bool 如果是中文则返回true，否则返回false
     */
    public static function isChinese($string): bool {
        return (bool) preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $string);
    }

    /**
     * 验证是否是合法的html标记
     * @access public
     * @param string $string 待验证的字串
     * @return bool 如果是合法的html标记则返回true，否则返回false
     */
    public static function isHtml($string): bool {
        return (bool) preg_match('/^<(.*)>.*|<(.*)\/>$/', $string);
    }

    /**
     * 验证是否是合法的客户端脚本
     * @access public
     * @param string $string 待验证的字串
     * @return bool 如果是合法的客户端脚本则返回true，否则返回false
     */
    public static function isScript($string): bool {
        return (bool) preg_match('/<script(?:.*?)>(?:[^\x00]*?)<\/script>/', $string);
    }

    /**
     * 验证是数字ＩＤ
     * @access public
     * @param int $number 需要被验证的数字
     * @return bool 如果大于等于0的整数数字返回true，否则返回false
     */
    public static function isNumberId($number): bool {
        return preg_match('/^[1-9][0-9]*$/i', $number);
    }

    /**
     * 验证是否是大陆手机号码
     * @access public
     * @param string $phone 待验证的号码
     * @return bool
     */
    public static function isMobilephone($phone): bool {
        return preg_match('/^13[0-9]{1}\d{8}|14[57]{1}\d{8}|15[012356789]{1}\d{8}|17[0678]{1}\d{8}|18[0-9]{1}\d{8}$/', $phone);
    }

    /**
     * 验证是否是不能为空
     * @access public
     * @param mixed $value 待判断的数据
     * @return bool 如果为空则返回false,不为空返回true
     */
    public static function isRequired($value): bool {
        return !empty($value);
    }

    /**
     * 查字符串是否是UTF8编码
     * @access public
     * @param string $string 字符
     * @return bool
     */
    public static function isUtf8($string): bool {
        $c = 0;
        $b = 0;
        $bits = 0;
        $len = strlen($string);
        for ($i = 0; $i < $len; $i++) {
            $c = ord($string[$i]);
            if ($c > 128) {
                if (($c >= 254)) {
                    return false;
                } elseif ($c >= 252) {
                    $bits = 6;
                } elseif ($c >= 248) {
                    $bits = 5;
                } elseif ($c >= 240) {
                    $bits = 4;
                } elseif ($c >= 224) {
                    $bits = 3;
                } elseif ($c >= 192) {
                    $bits = 2;
                } else {
                    return false;
                }
                if (($i + $bits) > $len) {
                    return false;
                }
                while ($bits > 1) {
                    $i++;
                    $b = ord($string[$i]);
                    if ($b < 128 || $b > 191) {
                        return false;
                    }
                    $bits--;
                }
            }
        }
        return true;
    }

}
