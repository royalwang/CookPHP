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
 * URL类
 * @author CookPHP <admin@cookphp.org>
 */
class Url {

    /**
     * 解析URL
     * @param string $url
     * @param array $params
     * @param bool $domain 是否显示域名和协议
     * @return string
     */
    public static function parse(string $url, $params = [], $domain = true): string {
        if (self::isUrl($url)) {
            return $url;
        }
        $array = parse_url($url);
        if (isset($array['fragment'])) {
            $anchor = $array['fragment'];
            if (false !== strpos($anchor, '?')) {
                list($anchor, $array['query']) = explode('?', $anchor, 2);
            }
            if (false !== strpos($anchor, '@')) {
                list($anchor, $hosts) = explode('@', $anchor, 2);
            }
        }

        if (empty($array['path']) || strpos($array['path'], '/') === false) {
            $array['path'] = Route::getController() . '/' . (!empty($array['path']) ? $array['path'] : Route::getAction()) . (Config::get('url.htmlsuffix') ? '.' . Config::get('url.htmlsuffix') : '');
        }

        $url = !empty($array['path']) ? $array['path'] : Route::getController() . '/' . Route::getAction() . (Config::get('url.htmlsuffix') ? '.' . Config::get('url.htmlsuffix') : '');
        $host = Request::host();
        if (isset($array['scheme'])) {
            if (Config::get('route.domain')) {
                $routeHost = $host === 'localhost' ? 'localhost' : strtolower($array['scheme']) . strstr($host, '.');
            } else {
                $url = ucfirst(strtolower($array['scheme'])) . '/' . $url;
            }
        } else {
            if (!Config::get('route.domain')) {
                $url = (strtolower(Config::get('route.project')) === strtolower(Route::getProject()) ? '' : Route::getProject() . '/') . $url;
            }
        }


        if (isset($array['query'])) {
            parse_str($array['query'], $query);
            $params = array_merge($query, $params);
            $str = '/';
            $depr = '/';
            foreach ($params as $var => $val) {
                $str .= $var . $depr . $val . $depr;
            }
            $url .= substr($str, 0, -1);
        }
        $url = (Request::isRewrite() ? Request::root() : Request::baseFile() ) . '/' . trim($url, '/');
        if (isset($anchor)) {
            $url .= '#' . $anchor;
        }
        if (isset($routeHost)) {
            $url = '//' . $domain . $url;
        } else {
            $url = ($domain ? Request::domain() : '') . $url;
        }
        return $url;
    }

    /**
     * 返回完整URL 不含 Request::baseFile()和后缀
     * @param string $url
     * @param bool $domain 是否显示域名和协议
     * @return string
     */
    public static function base(string $url, $domain = true): string {
        if (self::isUrl($url)) {
            return $url;
        }
        $url = Request::root() . '/' . ltrim($url, '/');
        $url = ($domain ? Request::domain() : '') . $url;
        return $url;
    }

    /**
     * 检测是否为完整url
     * @param string $url
     * @return bool
     */
    public static function isUrl($url): bool {
        return (bool) preg_match('/^(?:http(?:s)?:\/\/(?:[\w-]+\.)+[\w-]+(?:\:\d+)*+(?:\/[\w- .\/?%&=]*)?)$/', $url);
    }

    /**
     * 过滤段的恶意字符
     * @access public
     * @param string
     * @return string
     */
    public static function filter($str) {
        !empty($str) && !empty(Config::get('url.permittedurichars')) && !preg_match("|^[" . str_replace(['\\-', '\-'], '-', preg_quote(Config::get('url.permittedurichars'), '-')) . "]+$|i", $str) && Error::show('The URI you submitted has disallowed characters.', 400);
        return str_replace(['$', '(', ')', '%28', '%29'], ['&#36;', '&#40;', '&#41;', '&#40;', '&#41;'], $str);
    }

    /**
     * 删除URL后缀
     * @access	private
     * @return	void
     */
    public static function removeSuffix($url) {
        return !empty(Config::get('url.htmlsuffix')) ? preg_replace("|" . preg_quote(Config::get('url.htmlsuffix')) . "$|", "", $url) : preg_replace('/\.' . self::ext($url) . '$/i', '', $url);
    }

    /**
     * 拆分URL
     * @access public
     * @param string $url
     * @return array
     */
    public static function explode($url) {
        $urls = [];
        foreach (explode("/", preg_replace("|/*(.+?)/*$|", "\\1", $url)) as $val) {
            $val = trim(self::filter($val));
            if ($val != '') {
                $urls[] = $val;
            }
        }
        return $urls;
    }

    /**
     * 当前URL的访问后缀
     * @access public
     * @param string $url
     * @return string
     */
    public static function ext($url = null) {
        return pathinfo($url ?: Request::pathInfo(), PATHINFO_EXTENSION);
    }

}
