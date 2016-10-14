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

namespace Drivers\Upload;

/**
 * 上传文件
 * @author CookPHP <admin@cookphp.org>
 */
class Local {

    /**
     * 上传文件根目录
     * @var string
     */
    private $rootpath;

    /**
     * 上传配置
     * @var array
     */
    private $config = [];

    /**
     * 语言包
     * @var array
     */
    private $lang = [];

    /**
     * 本地上传错误信息
     * @var string
     */
    private $error = ''; //上传错误信息

    /**
     * 构造函数，用于设置上传根路径
     */

    public function __construct($config = null, $lang) {
        $this->config = $config;
        $this->lang = $lang;
    }

    /**
     * 检测上传根目录
     * @param string $rootpath   根目录
     * @return boolean true-检测通过，false-检测失败
     */
    public function checkRootPath(string $rootpath): bool {
        if (!(is_dir($rootpath) && is_writable($rootpath))) {
            $this->error = $this->lang['no_filepath'];
            return false;
        }
        $this->rootpath = $rootpath;
        return true;
    }

    /**
     * 检测上传目录
     * @param  string $savepath 上传目录
     * @return boolean          检测结果，true-通过，false-失败
     */
    public function checkSavePath(string $savepath): bool {
        /* 检测并创建目录 */
        if (!$this->mkdir($savepath)) {
            return false;
        } else {
            /* 检测目录是否可写 */
            if (!is_writable($this->rootpath . $savepath)) {
                $this->error = $this->lang['not_writable'];
                return false;
            } else {
                return true;
            }
        }
    }

    /**
     * 保存指定文件
     * @param  array   $file    保存的文件信息
     * @param  boolean $replace 同名文件是否覆盖
     * @return boolean          保存状态，true-成功，false-失败
     */
    public function save(array $file, bool $replace = true): bool {
        $filename = $this->rootpath . $file['savepath'] . $file['savename'];
        /* 不覆盖同名文件 */
        if (!$replace && is_file($filename)) {
            $this->error = $this->lang['bad_filename'];
            return false;
        }
        /* 移动文件 */
        if (!move_uploaded_file($file['tmp_name'], $filename)) {
            $this->error = $this->lang['destination_error'];
            return false;
        }
        return true;
    }

    /**
     * 创建目录
     * @param  string $savepath 要创建的穆里
     * @return boolean          创建状态，true-成功，false-失败
     */
    public function mkdir(string $savepath): bool {
        $dir = $this->rootpath . $savepath;
        if (!\Helpers\File::makeDirectory($dir)) {
            $this->error = $this->lang['no_filepath'];
            return false;
        }
        return true;
    }

    /**
     * 获取最后一次上传错误信息
     * @return string 错误信息
     */
    public function getError(): string {
        return $this->error;
    }

}
