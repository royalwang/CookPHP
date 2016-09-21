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

namespace Drivers\View;

use Core\{
    Config,
    Loader
};

/**
 * Smarty
 * @author CookPHP <admin@cookphp.org>
 * @link http://www.smarty.net/docs/zh_CN/
 */
class Smarty implements \Interfaces\View {

    protected $smarty;

    /**
     * 初始化
     */
    public function __construct() {
        if (!$this->smarty) {
            Loader::requireOnce(__DIR__ . DS . 'Smarty' . DS . 'Smarty.class.php');
            $this->smarty = Loader::initialize('\\Smarty');
            //$this->smarty->caching = Config::get('view.compilecache');
            //$this->smarty->debugging = true;
            $this->smarty->setCacheDir(Config::get('view.cachedir'))->setCompileDir(Config::get('view.compiledir'))->setTemplateDir(__APP__ . Route::getProject() . DS . 'View')->setTemplateDir(__COMMON__ . 'View')->setTemplateDir(__COOK__ . 'View');
            foreach ((array) Config::get('view.config') as $key => $value) {
                $this->smarty->$key = $value;
            }
        }
    }

    /**
     * 返回驱动
     */
    public function engine() {
        return $this->smarty;
    }

    public function render($template, $data = null) {
        $template .= $this->getSuffix();
        $data = $data instanceof \ArrayObject ? $data->getArrayCopy() : (array) $data;
        foreach ($data as $key => $value) {
            $this->smarty->assign($key, $value);
        }
        $content = $this->smarty->fetch($template);
        if (Config::get('view.compresshtml')) {
            \Helpers\Format::html($content);
        }
        return $content;
    }

    public function supports($template, $type = null): bool {
        return in_array($type ?: pathinfo($template, PATHINFO_EXTENSION), [$type ?: 'tpl']);
    }

    public function getSuffix(): string {
        return '.tpl';
    }

}
