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
 * 客户请求分析类
 * @author CookPHP <admin@cookphp.org>
 */
class Request {

    /**
     * 当前的请求类型
     * @access public
     * @return string
     */
    public static function method(): string {
        return IS_CLI ? 'GET' : strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * 是否为GET请求
     * @access public
     * @return bool
     */
    public static function isGet(): bool {
        return self::method() === 'GET';
    }

    /**
     * 是否为POST请求
     * @access public
     * @return bool
     */
    public static function isPost(): bool {
        return self::method() === 'POST';
    }

    /**
     * 是否为PUT请求
     * @access public
     * @return bool
     */
    public static function isPut(): bool {
        return self::method() === 'PUT';
    }

    /**
     * 是否为DELTE请求
     * @access public
     * @return bool
     */
    public static function isDelete(): bool {
        return self::method() === 'DELETE';
    }

    /**
     * 是否为HEAD请求
     * @access public
     * @return bool
     */
    public static function isHead(): bool {
        return self::method() === 'HEAD';
    }

    /**
     * 是否为PATCH请求
     * @access public
     * @return bool
     */
    public static function isPatch(): bool {
        return self::method() === 'PATCH';
    }

    /**
     * 是否为OPTIONS请求
     * @access public
     * @return bool
     */
    public static function isOptions(): bool {
        return self::method() === 'OPTIONS';
    }

    /**
     * 是否为Windows系统
     * @access public
     * @return bool
     */
    public static function isWin(): bool {
        return strstr(PHP_OS, 'WIN') ? true : false;
    }

    /**
     * 当前是否Ajax请求
     * @access public
     * @return bool
     */
    public static function isAjax(): bool {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? true : false;
    }

    /**
     * 当前是否Pjax请求
     * @access public
     * @return bool
     */
    public static function isPjax(): bool {
        return !empty($_SERVER['HTTP_X_PJAX']) ? true : false;
    }

    /**
     * 检测是否使用手机访问
     * @access public
     * @return bool
     */
    public static function isMobile(): bool {
        return (isset($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], "wap")) || (strpos(strtoupper($_SERVER['HTTP_ACCEPT']), "VND.WAP.WML")) || (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])) || (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $_SERVER['HTTP_USER_AGENT'])) ? true : false;
    }

    /**
     * 判断是否是通过微信访问
     *
     * @access public
     * @return bool
     */
    public static function isWeixin(): bool {
        return isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ? true : false;
    }

    /**
     * 判断是否是通过微信访问
     *
     * @access public
     * @return bool
     */
    public static function isWeChat(): bool {
        return self::isWeixin();
    }

    /**
     * 返回客户端IP
     * @access public
     * @return string
     */
    public static function ip(): string {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) {
                unset($arr[$pos]);
            }
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $long = sprintf("%u", ip2long($ip));
        return $long ? $ip : '0.0.0.0';
    }

    /**
     * 当前是否ssl
     * @access public
     * @return bool
     */
    public static function isSsl(): bool {
        return (isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) || (isset($_SERVER['REQUEST_SCHEME']) && 'https' == $_SERVER['REQUEST_SCHEME']) || (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && 'https' == $_SERVER['HTTP_X_FORWARDED_PROTO']) ? true : false;
    }

    /**
     * 检查是否已安装Apache的mod_rewrite
     * @access public
     * @staticvar bool $isRewrite
     * @return bool
     */
    public static function isRewrite(): bool {
        static $isRewrite = null;
        if ($isRewrite === null) {
            $isRewrite = function_exists('apache_get_modules') ? in_array('mod_rewrite', apache_get_modules()) : isset($_SERVER['HTTP_MOD_REWRITE']) && (strtolower($_SERVER['HTTP_MOD_REWRITE']) == 'on');
        }
        return $isRewrite;
    }

    /**
     * 获取当前包含协议的域名
     * @access public
     * @param string $auto 是否自动协议
     * @return string
     */
    public static function domain($auto = true): string {
        return ($auto ? '//' : self::scheme() . '://') . self::host();
    }

    /**
     * 当前URL地址中的scheme参数
     * @access public
     * @return string
     */
    public static function scheme(): string {
        return self::isSsl() ? 'https' : 'http';
    }

    /**
     * 获取当前请求的时间
     * @access public
     * @param bool $float 是否使用浮点类型
     * @return integer|float
     */
    public static function time($float = false) {
        return $float ? $_SERVER['REQUEST_TIME_FLOAT'] : $_SERVER['REQUEST_TIME'];
    }

    /**
     * 获取当前完整URL 包括QUERY_STRING
     * @access public
     * @param string|true $domain true 带域名获取
     * @return string
     */
    public static function url($domain = false) {
        static $url = null;
        if (!$url) {
            $url = IS_CLI ? isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '' : ( $_SERVER['HTTP_X_REWRITE_URL'] ?? ($_SERVER['REQUEST_URI'] ?? (isset($_SERVER['ORIG_PATH_INFO']) ? ($_SERVER['ORIG_PATH_INFO'] . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '')) : '')));
        }
        return $domain ? self::domain() . $url : $url;
    }

    /**
     * 获取当前URL 不含QUERY_STRING
     * @access public
     * @param string|true $domain true 带域名获取
     * @return string
     */
    public static function baseUrl($domain = false) {
        static $baseUrl = null;
        if (!$baseUrl) {
            $str = self::url();
            $baseUrl = strpos($str, '?') ? strstr($str, '?', true) : $str;
        }
        return $domain ? self::domain() . $baseUrl : $baseUrl;
    }

    /**
     * 获取当前执行的文件 SCRIPT_NAME
     * @access public
     * @param string|true $domain true 带域名获取
     * @return string
     */
    public static function baseFile($domain = false) {
        static $baseFile = null;
        if (!$baseFile) {
            $url = '';
            if (!IS_CLI) {
                $script_name = basename($_SERVER['SCRIPT_FILENAME']);
                if (basename($_SERVER['SCRIPT_NAME']) === $script_name) {
                    $url = $_SERVER['SCRIPT_NAME'];
                } elseif (basename($_SERVER['PHP_SELF']) === $script_name) {
                    $url = $_SERVER['PHP_SELF'];
                } elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $script_name) {
                    $url = $_SERVER['ORIG_SCRIPT_NAME'];
                } elseif (($pos = strpos($_SERVER['PHP_SELF'], '/' . $script_name)) !== false) {
                    $url = substr($_SERVER['SCRIPT_NAME'], 0, $pos) . '/' . $script_name;
                } elseif (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) === 0) {
                    $url = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
                }
            }
            $baseFile = $url;
        }
        return $domain ? self::domain() . $baseFile : $baseFile;
    }

    /**
     * 获取URL访问根地址
     * @access public
     * @param string|true $domain true 带域名获取
     * @return string
     */
    public static function root($domain = false) {
        static $root = null;
        if (!$root) {
            $file = dirname(self::baseFile());
            $root = rtrim($file, '\\/');
        }
        return ($domain ? self::domain() : '') . $root;
    }

    /**
     * 获取当前请求URL的pathinfo信息（含URL后缀）
     * @access public
     * @return string
     */
    public static function pathInfo() {
        static $pathinfo = null;
        if (is_null($pathinfo)) {
            if (IS_CLI) {
                $_SERVER['PATH_INFO'] = $_SERVER['argv'][1] ?? '';
            } elseif (!isset($_SERVER['PATH_INFO'])) {
                foreach (Config::get('route.pathinfofetch') as $type) {
                    if (!empty($_SERVER[$type])) {
                        $_SERVER['PATH_INFO'] = (0 === strpos($_SERVER[$type], $_SERVER['SCRIPT_NAME'])) ? substr($_SERVER[$type], strlen($_SERVER['SCRIPT_NAME'])) : $_SERVER[$type];
                        break;
                    }
                }
            }
            $pathinfo = empty($_SERVER['PATH_INFO']) ? '/' : ltrim($_SERVER['PATH_INFO'], '/');
        }
        return $pathinfo;
    }

    /**
     * 获取当前请求URL的pathinfo信息(不含URL后缀)
     * @access public
     * @return string
     */
    public static function path() {
        static $path = null;
        if (is_null($path)) {
            $path = Url::removeSuffix(self::pathInfo());
        }
        return $path;
    }

    /**
     * 当前请求URL地址中的query参数
     * @access public
     * @return string
     */
    public static function query() {
        return $_SERVER['QUERY_STRING'] ?? '';
    }

    /**
     * 当前请求的host
     * @access public
     * @return string
     */
    public static function host() {
        return strtolower($_SERVER['HTTP_HOST']);
    }

    /**
     * 当前请求URL地址中的port参数
     * @access public
     * @return integer
     */
    public static function port() {
        return $_SERVER['SERVER_PORT'] ?? 80;
    }

    /**
     * 当前请求 SERVER_PROTOCOL
     * @access public
     * @return integer
     */
    public static function protocol() {
        return $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
    }

    /**
     * 当前请求 REMOTE_PORT
     * 连接到服务器时所使用的端口
     * @access public
     * @return integer
     */
    public static function remotePort() {
        return $_SERVER['REMOTE_PORT'];
    }

    /**
     * 返回客户端的HTTP
     * @access public
     * @return string
     */
    public static function getHttpVersion() {
        static $_httpVersion = null;
        return $_httpVersion ?: ($_httpVersion = isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] === 'HTTP/1.0' ? '1.0' : '1.1');
    }

}
