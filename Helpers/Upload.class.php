<?php

/**
 * CookPHP framework
 *
 * @name CookPHP framework
 * @package CookPHP
 * @author CookPHP <admin@cookphp.org>
 * @version 0.0.1 Beta
 * @link http://www.cookphp.org
 * @copyright cookphp.org
 * @license <a href="http://www.cookphp.org">CookPHP</a>
 */

namespace Helpers;

/**
 * 上传类
 * @author CookPHP <admin@cookphp.org>
 */
class Upload {

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
     * 上传错误信息
     * @var string
     */
    private $error = ''; //上传错误信息

    /**
     * 上传驱动实例
     * @var Object
     */
    private $uploader;

    /**
     * 构造方法，用于构造上传实例
     * @param array  $config 配置
     * @param string $driver 要使用的上传驱动 LOCAL-本地上传驱动，FTP-FTP上传驱动
     */
    public function __construct($config = [], $driver = '', $driverConfig = null) {
        /* 获取配置 */
        $this->config = array_merge(\Core\Config::all('upload'), $config);
        /* 获取语言包 */
        $this->lang = \Core\Lang::all('upload');

        /* 设置上传驱动 */
        $this->setDriver($driver, $driverConfig);

        /* 调整配置，把字符串配置参数转换为数组 */
        if (!empty($this->config['mimes'])) {
            if (is_string($this->config['mimes'])) {
                $this->config['mimes'] = explode(',', $this->config['mimes']);
            }
            $this->config['mimes'] = array_map('strtolower', $this->mimes);
        }
        if (!empty($this->config['exts'])) {
            if (is_string($this->exts)) {
                $this->config['exts'] = explode(',', $this->exts);
            }
            $this->config['exts'] = array_map('strtolower', $this->exts);
        }
    }

    /**
     * 使用 $this->name 获取配置
     * @param  string $name 配置名称
     * @return multitype    配置值
     */
    public function __get($name) {
        return $this->config[$name];
    }

    public function __set($name, $value) {
        if (isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
    }

    public function __isset($name) {
        return isset($this->config[$name]);
    }

    /**
     * 获取最后一次上传错误信息
     * @return string 错误信息
     */
    public function getError() {
        return $this->error;
    }

    /**
     * 上传单个文件
     * @param  array  $file 文件数组
     * @return array        上传成功后的文件信息
     */
    public function uploadOne($file) {
        $info = $this->upload((array) $file);
        return $info ? $info[0] : $info;
    }

    /**
     * 上传文件
     * @param 文件信息数组 $files ，通常是 $_FILES数组
     */
    public function upload($files = '') {
        if ('' === $files) {
            $files = $_FILES;
        }
        if (empty($files)) {
            $this->error = $this->lang['no_file_selected'];
            return false;
        }

        /* 检测上传根目录 */
        if (!$this->uploader->checkRootPath($this->rootpath)) {
            $this->error = $this->uploader->getError();
            return false;
        }

        /* 检查上传目录 */
        if (!$this->uploader->checkSavePath($this->savepath)) {
            $this->error = $this->uploader->getError();
            return false;
        }

        /* 逐个检测并上传文件 */
        $info = [];
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
        }
        // 对上传文件数组信息处理
        $files = $this->dealFiles($files);
        foreach ($files as $key => $file) {
            $file['name'] = strip_tags($file['name']);
            if (!isset($file['key']))
                $file['key'] = $key;
            /* 通过扩展获取文件类型，可解决FLASH上传$FILES数组返回文件类型错误的问题 */
            if (isset($finfo)) {
                $file['type'] = finfo_file($finfo, $file['tmp_name']);
            }

            /* 获取上传文件后缀，允许上传无后缀文件 */
            $file['ext'] = pathinfo($file['name'], PATHINFO_EXTENSION);

            /* 文件上传检测 */
            if (!$this->check($file)) {
                continue;
            }

            /* 获取文件hash */
            if ($this->hash) {
                $file['md5'] = md5_file($file['tmp_name']);
                $file['sha1'] = sha1_file($file['tmp_name']);
            }

            /* 调用回调函数检测文件是否存在 */
            if ($this->callback && ($data = call_user_func($this->callback, $file))) {
                if (file_exists('.' . $data['path'])) {
                    $info[$key] = $data;
                    continue;
                } elseif ($this->removetrash) {
                    call_user_func($this->removetrash, $data); //删除垃圾据
                }
            }

            /* 生成保存文件名 */
            $savename = $this->getSaveName($file);
            if (false == $savename) {
                continue;
            } else {
                $file['savename'] = $savename;
            }

            /* 检测并创建子目录 */
            $subpath = $this->getSubPath($file['name']);
            if (false === $subpath) {
                continue;
            } else {
                $file['savepath'] = $this->savepath . $subpath;
            }

            /* 对图像文件进行严格检测 */
            $ext = strtolower($file['ext']);
            if (in_array($ext, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'swf'])) {
                $imginfo = getimagesize($file['tmp_name']);
                if (empty($imginfo) || ($ext == 'gif' && empty($imginfo['bits']))) {
                    $this->error = $this->lang['invalid_filetype'];
                    continue;
                }
            }

            /* 保存文件 并记录保存成功的文件 */
            if ($this->uploader->save($file, $this->replace)) {
                unset($file['error'], $file['tmp_name']);
                $info[$key] = $file;
            } else {
                $this->error = $this->uploader->getError();
            }
        }
        if (isset($finfo)) {
            finfo_close($finfo);
        }
        return !empty($info) ? $info : false;
    }

    /**
     * 转换上传文件数组变量为正确的方式
     * @access private
     * @param array $files  上传的文件变量
     * @return array
     */
    private function dealFiles($files) {
        $fileArray = [];
        $n = 0;
        foreach ($files as $key => $file) {
            if (is_array($file['name'])) {
                $keys = array_keys($file);
                $count = count($file['name']);
                for ($i = 0; $i < $count; $i++) {
                    $fileArray[$n]['key'] = $key;
                    foreach ($keys as $_key) {
                        $fileArray[$n][$_key] = $file[$_key][$i];
                    }
                    $n++;
                }
            } else {
                $fileArray = $files;
                break;
            }
        }
        return $fileArray;
    }

    /**
     * 设置上传驱动
     * @param string $driver 驱动名称
     * @param array $config 驱动配置     
     */
    private function setDriver($driver = null, $config = null) {
        $driver = $driver ?: $this->config['driver'];
        $config = $config ?: ($this->config['driverconfig']);
        $class = strpos($driver, '\\') ? $driver : '\\Drivers\\Upload\\' . ucfirst(strtolower($driver));
        if (class_exists($class)) {
            $this->uploader = new $class($config, $this->lang);
        } else {
            \Core\Error::show("Unable to load class:{$class}");
        }
    }

    /**
     * 检查上传的文件
     * @param array $file 文件信息
     */
    private function check($file): bool {
        /* 文件上传失败，捕获错误代码 */
        if ($file['error']) {
            $this->error($file['error']);
            return false;
        }

        /* 无效上传 */
        if (empty($file['name'])) {
            $this->error = $this->lang['userfile_not_set'];
        }

        /* 检查是否合法上传 */
        if (!is_uploaded_file($file['tmp_name'])) {
            $this->error = $this->lang['try_again'];
            return false;
        }

        /* 检查文件大小 */
        if (!$this->checkSize($file['size'])) {
            $this->error = $this->lang['invalid_filesize'];
            return false;
        }

        /* 检查文件Mime类型 */
        //TODO:FLASH上传的文件获取到的mime类型都为application/octet-stream
        if (!$this->checkMime($file['type'])) {
            $this->error = $this->lang['invalid_filetype'];
            return false;
        }

        /* 检查文件后缀 */
        if (!$this->checkExt($file['ext'])) {
            $this->error = $this->lang['invalid_filetype'];
            return false;
        }

        /* 通过检测 */
        return true;
    }

    /**
     * 获取错误代码信息
     * @param string $errorNo  错误号
     */
    private function error($errorNo) {
        switch ($errorNo) {
            case 1:
                $this->error = $this->lang['file_exceeds_limit'];
                break;
            case 2:
                $this->error = $this->lang['file_exceeds_form_limit'];
                break;
            case 3:
                $this->error = $this->lang['file_partial'];
                break;
            case 4:
                $this->error = $this->lang['try_again'];
                break;
            case 6:
                $this->error = $this->lang['no_temp_directory'];
                break;
            case 7:
                $this->error = $this->lang['unable_to_write_file'];
                break;
            default:
                $this->error = $this->lang['uploaded_unknown'];
        }
    }

    /**
     * 检查文件大小是否合法
     * @param integer $size 数据
     */
    private function checkSize($size) {
        return !($size > $this->maxsize) || (0 == $this->maxsize);
    }

    /**
     * 检查上传的文件MIME类型是否合法
     * @param string $mime 数据
     */
    private function checkMime($mime) {
        return empty($this->config['mimes']) ? true : in_array(strtolower($mime), $this->mimes);
    }

    /**
     * 检查上传的文件后缀是否合法
     * @param string $ext 后缀
     */
    private function checkExt($ext) {
        return empty($this->config['exts']) ? true : in_array(strtolower($ext), $this->exts);
    }

    /**
     * 根据上传文件命名规则取得保存文件名
     * @param string $file 文件信息
     */
    private function getSaveName($file) {
        $rule = $this->savename;
        if (empty($rule)) {
            $filename = substr(pathinfo("_{$file['name']}", PATHINFO_FILENAME), 1);
            $savename = $filename;
        } else {
            $savename = $this->getName($rule, $file['name']);
            if (empty($savename)) {
                $this->error = $this->lang['file_naming_errors'];
                return false;
            }
        }

        /* 文件保存后缀，支持强制更改文件后缀 */
        $ext = empty($this->config['saveExt']) ? $file['ext'] : $this->saveExt;

        return $savename . '.' . $ext;
    }

    /**
     * 获取子目录的名称
     * @param array $filename  上传的文件信息
     */
    private function getSubPath($filename) {
        $subpath = '';
        $rule = $this->subname;
        if ($this->autosub && !empty($rule)) {
            $subpath = $this->getName($rule, $filename) . DS;

            if (!empty($subpath) && !$this->uploader->mkdir($this->savepath . $subpath)) {
                $this->error = $this->uploader->getError();
                return false;
            }
        }
        return $subpath;
    }

    /**
     * 根据指定的规则获取文件或目录名称
     * @param  array  $rule     规则
     * @param  string $filename 原文件名
     * @return string           文件或目录名称
     */
    private function getName($rule, $filename) {
        $name = '';
        if (is_array($rule)) { //数组规则
            $func = $rule[0];
            $param = (array) $rule[1];
            foreach ($param as &$value) {
                $value = str_replace('__FILE__', $filename, $value);
            }
            $name = call_user_func_array($func, $param);
        } elseif (is_string($rule)) { //字符串规则
            if (function_exists($rule)) {
                $name = call_user_func($rule);
            } else {
                $name = $rule;
            }
        }
        return $name;
    }

}
