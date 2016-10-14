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

namespace Drivers\View;

/**
 * 原始PHP
 * @author CookPHP <admin@cookphp.org>
 */
class Php implements \Interfaces\View {

    /**
     * 返回驱动
     */
    public function engine() {
        return $this;
    }

    public function render($template, $data = null) {
        $template .= $this->getSuffix();
        if (!file_exists($template)) {
            return '';
        }
        $data = $data instanceof \ArrayObject ? $data->getArrayCopy() : (array) $data;
        extract($data);
        ob_start();
        ob_implicit_flush(0);
        try {
            require $template;
        } catch (\Exception $exception) {
            ob_end_clean();
            throw $exception;
        }
        $content = ob_get_clean();
        if (Config::get('view.compresshtml')) {
            \Helpers\Format::html($content);
        }
        return $content;
    }

    public function supports($template, $type = null): bool {
        return in_array($type ?: pathinfo($template, PATHINFO_EXTENSION), [$type ?: 'php']);
    }

    public function getSuffix(): string {
        return '.php';
    }

}
