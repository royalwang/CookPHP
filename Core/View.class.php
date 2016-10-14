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
 * 视图类
 * @author CookPHP <admin@cookphp.org>
 */
class View extends Common {

    private $_vars = [];

    public function __set($name, $value) {
        $this->assign($name, $value);
    }

    public function __get($name) {
        return $this->getVar($name);
    }

    /**
     * 返回驱动
     */
    public function engine() {
        return $this->view()->engine();
    }

    /**
     * 赋值
     * @access public
     * @param string|array $name
     * @param mixed $value
     */
    public function assign($name, $value = null) {
        is_array($name) ? ($this->_vars = array_merge($this->_vars, $name)) : ($this->_vars[$name] = $value);
    }

    /**
     * 获取模板变量
     * @access public
     * @param string $name
     * @return mixed
     */
    public function getVar($name = '') {
        return $name === '' ? $this->_vars : ($this->_vars[$name] ?? null);
    }

    /**
     * 渲染模板
     * @access public
     * @param string $template 模板
     * @param null|array $data 赋值
     * @param bool $return 是否直接返回，默认false输出
     */
    public function render($template = '', $data = null, $return = false) {
        $this->replaceTemplate($template);
        $view = $this->view()->render($template, (!empty($data) ? array_merge($this->_vars, (array) $data) : $this->_vars));
        if ($return) {
            return $view;
        } else {
            echo $view;
        }
    }

    /**
     * 取得输出内容
     * @access public
     * @param string $template 模板
     * @return string
     */
    public function fetch($template = '') {
        return $this->render($template, $this->_vars, true);
    }

    /**
     * 显示输出内容
     * @access public
     * @param string $template 模板
     * @param string $template
     */
    public function display($template = '') {
        $this->render($template, $this->_vars, false);
    }

    /**
     * 解析模板名称
     * @access public
     * @param string $template
     * @return string
     */
    private function replaceTemplate(&$template) {
        if (empty($template)) {
            $template = Route::getController() . ':' . Route::getAction();
        } elseif (is_readable($template)) {
            return $template;
        } elseif (stristr($template, ':') === false) {
            $template = Route::getController() . ':' . $template;
        }
        $template = str_ireplace(':', DS, trim($template, ':'));
//        if (pathinfo($template, PATHINFO_EXTENSION) !== $this->view()->getSuffix()) {
//            $template .= $this->view()->getSuffix();
//        }
//        if (!$this->view()->supports($template.$this->view()->getSuffix())) {
//            Error::show('Error View Handler:' . $template, 500);
//        }
        return $template;
    }

}
