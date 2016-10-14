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

namespace Helpers;

/**
 * 文件组件
 * @author CookPHP <admin@cookphp.org>
 */
class File {

    /**
     * 检测文件是否存在
     * @access public
     * @param string $file
     * @return bool
     */
    public static function has($file): bool {
        return is_file($file) ? true : false;
    }

    /**
     * 删除一个文件
     * @access public
     * @param string $file 文件名，绝对地址
     */
    public static function delete($file) {
        self::remove($file);
    }

    /**
     * 删除一个文件
     * @access public
     * @param string $file 文件名，绝对地址
     */
    public static function remove($file) {
        self::has($file) && unlink($file);
    }

    /**
     * 获取的文件的内容
     * @access public
     * @param string $file
     * @return string|null
     */
    public static function get($file) {
        return self::isFile($file) ? file_get_contents($file) : null;
    }

    /**
     * 获取返回文件
     * @access public
     * @param string $file
     * @return mixed
     */
    public static function loadFile($file) {
        return self:: isFile($file) ? require $file : null;
    }

    /**
     * 唯一包含并运行指定文件
     * @access public
     * @param string $file
     * @return mixed
     */
    public static function requireOnce($file) {
        if (self:: isFile($file)) {
            require_once $file;
        }
    }

    /**
     * 加载文件
     * @param string $file
     * @return bool 
     */
    protected function requireFile($file): bool {
        if (file_exists($file)) {
            require $file;
            return true;
        }
        return false;
    }

    /**
     * 写一个文件的内容
     * @access public
     * @param string $file
     * @param string $contents
     * @return int
     */
    public static function put($file, $contents) {
        return self::makeDirectory(self::driname($file)) && file_put_contents($file, $contents);
    }

    /**
     * 写一个文件的内容
     * @access public
     * @param string $file
     * @param string $contents
     * @return int
     */
    public static function set($file, $contents) {
        return self::put($file, $contents);
    }

    /**
     * 追加数据而不是覆盖
     * @access public
     * @param string $file
     * @param string $data
     * @return int
     */
    public static function append($file, $data) {
        return self::makeDirectory(self::driname($file)) && file_put_contents($file, $data, FILE_APPEND);
    }

    /**
     * 重命名一个文件或目录
     * @access public
     * @param string $path  源文件或目录
     * @param string $target 新的名字
     * @return bool
     */
    public static function move($path, $target): bool {
        return rename($path, $target);
    }

    /**
     * — 拷贝文件或目录
     * @access public
     * @param string $path 源文件或目录
     * @param string $target 目标路径
     * @return bool
     */
    public static function copy($path, $target): bool {
        return copy($path, $target);
    }

    /**
     * 获取路径中的目录部分
     * @access public
     * @param string $path
     * @return string
     */
    public static function driname($path): string {
        return pathinfo($path, PATHINFO_DIRNAME);
    }

    /**
     * 获取路径中文件名
     * @access public
     * @param string $path
     * @return string
     */
    public static function name($path): string {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * 获取路径文件扩展名
     * @access public
     * @param string $path
     * @return string
     */
    public static function extension($path): string {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * 获取给定文件的文件类型
     * @access public
     * @param string $path
     * @return string
     * fifo，char，dir，block，link，file，unknown
     */
    public static function type($path): string {
        return filetype($path);
    }

    /**
     * 获取给定文件的MIME类型
     * @access public
     * @param string $file
     * @return string|false
     */
    public static function mimeType($file) {
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file);
    }

    /**
     * 获取文件的文件大小
     * @access public
     * @param string $file
     * @return int
     */
    public static function size($file): int {
        return filesize($file);
    }

    /**
     * 获取文件的最后修改时间
     * @access public
     * @param string $file
     * @return int
     */
    public static function lastModified($file): int {
        return filemtime($file);
    }

    /**
     * 检测是否为文件夹
     * @access public
     * @param string $directory
     * @return bool
     */
    public static function isDirectory($directory): bool {
        return is_dir($directory);
    }

    /**
     * 检测文件夹是否可读写
     * @access public
     * @param string $path
     * @return bool
     */
    public static function isWritable($path): bool {
        return is_writable($path);
    }

    /**
     * 检测是否为文件
     * @access public
     * @param string $file
     * @return bool
     */
    public static function isFile($file): bool {
        return self::has($file);
    }

    /**
     * 寻找与模式匹配的文件路径
     * @access public
     * @param string $pattern
     * @param int    $flags
     * GLOB_MARK - 在每个返回的项目中加一个斜线
     * GLOB_NOSORT - 按照文件在目录中出现的原始顺序返回（不排序）
     * GLOB_NOCHECK - 如果没有文件匹配则返回用于搜索的模式
     * GLOB_NOESCAPE - 反斜线不转义元字符
     * GLOB_BRACE - 扩充 {a,b,c} 来匹配 'a'，'b' 或 'c'
     * GLOB_ONLYDIR - 仅返回与模式匹配的目录项
     * GLOB_ERR - 停止并读取错误信息（比如说不可读的目录），默认的情况下忽略所有错误
     * @return array
     */
    public static function glob($pattern, $flags = 0) {
        return glob($pattern, $flags);
    }

    /**
     * 创建目录
     * @access public
     * @param string $path 路径
     * @param string $mode 权限
     * @return string 如果已经存在则返回YES，否则为flase
     */
    public static function makeDirectory($path, $mode = 0755) {
        if (is_dir($path)) {
            return true;
        } else {
            $_path = dirname($path);
            if ($_path !== $path) {
                self::makeDirectory($_path, $mode);
            }
            return mkdir($path, $mode);
        }
    }

    /**
     * 目录从一个位置复制一个到另一位置
     * @access public
     * @param string $directory
     * @param string $destination
     * @param int    $options
     * @return bool
     */
    public static function copyDirectory($directory, $destination, $options = null): bool {
        if (!self:: isDirectory($directory)) {
            return false;
        }

        $options = $options ?: \FilesystemIterator::SKIP_DOTS;

        if (!self:: isDirectory($destination)) {
            self:: makeDirectory($destination, 0777, true);
        }

        $items = new \FilesystemIterator($directory, $options);

        foreach ($items as $item) {
            $target = $destination . '/' . $item->getBasename();

            if ($item->isDir()) {
                $path = $item->getPathname();

                if (!self:: copyDirectory($path, $target, $options)) {
                    return false;
                }
            } else {
                if (!self:: copy($item->getPathname(), $target)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 递归删除一个目录。
     * @access public
     * @param string $directory
     * @param bool   $preserve 目录本身保留 默认 false
     * @return bool
     */
    public static function deleteDirectory($directory, $preserve = false): bool {
        if (!self:: isDirectory($directory)) {
            return false;
        }
        $items = new \FilesystemIterator($directory);
        foreach ($items as $item) {
            if ($item->isDir() && !$item->isLink()) {
                self:: deleteDirectory($item->getPathname());
            } else {
                self:: delete($item->getPathname());
            }
        }

        if (!$preserve) {
            @rmdir($directory);
        }

        return true;
    }

    /**
     * 清空目录及目录下所有文件和文件夹
     * @access public
     * @param string $directory
     * @return bool
     */
    public static function cleanDirectory($directory): bool {
        return self:: deleteDirectory($directory, true);
    }

    /**
     * 下载文件
     * @access public
     * @param string $fullPath
     */
    public static function download($fullPath) {
        if ($fd = fopen($fullPath, 'r')) {
            $fsize = filesize($fullPath);
            $path_parts = pathinfo($fullPath);
            $ext = strtolower($path_parts['extension']);
            switch ($ext) {
                case 'pdf':
                    header('Content-type: application/pdf');
                    header('Content-Disposition: attachment; filename="' . $path_parts['basename'] . '"');
                    break;
                default:
                    header('Content-type: application/octet-stream');
                    header('Content-Disposition: filename="' . $path_parts['basename'] . '"');
            }
            header("Content-length: $fsize");
            header('Cache-control: private');
            while (!feof($fd)) {
                $buffer = fread($fd, 2048);
                echo $buffer;
            }
        }
        fclose($fd);
    }

}
