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

namespace Core;

/**
 * 控制器类
 * @author CookPHP <admin@cookphp.org>
 */
abstract class Controller extends View {

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param string $message 错误信息
     */
    protected function error($message, $url = '') {
        $this->ajaxReturn($message, 0, '', $url);
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param string $message 提示信息
     */
    protected function success($message, $url = '') {
        $this->ajaxReturn($message, 1, '', $url);
    }

    /**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $info 提示信息
     * @param boolean $status 返回状态
     */
    protected function ajaxReturn($info = '', int $status = 1, $data = '', $url = '') {
        $result = [];
        $result['status'] = (int) $status;
        if (is_string($info)) {
            $result['info'] = $info;
        }
        if (!empty($url)) {
            $result['url'] = $url;
        }
        if (!empty($data)) {
            $result['data'] = $data;
        }
        if (is_array($info)) {
            $result['data'] = $info;
        }
        header('Content-type:application/json;charset=' . CHARSET);
        exit(json_encode($result));
    }

    /**
     * URL重定向
     * @access protected
     * @param string $url 跳转的URL表达式
     * @param array $params 其它URL参数
     * @param string $msg 跳转提示信息
     */
    protected function redirect($url, $params = []) {
        Loader::redirect($this->url($url, $params));
    }

}
