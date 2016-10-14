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
 * 字符串或文件
 * @author CookPHP <admin@cookphp.org>
 */
class String implements \Interfaces\View {

    /**
     * 返回驱动
     */
    public function engine() {
        return $this;
    }

    public function render($template, $data = null) {
        $template .= $this->getSuffix();
        if (file_exists($template)) {
            $template = file_get_contents($template);
        }
        $data = $data instanceof \ArrayObject ? $data->getArrayCopy() : (array) $data;
        $content = strtr($template, $data);
        if (Config::get('view.compresshtml')) {
            \Helpers\Format::html($content);
        }
        return $content;
    }

    public function supports($template, $type = null): bool {
        return true;
    }

    public function getSuffix(): string {
        return '';
    }

}
