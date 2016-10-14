<?php

/**
 * 上传文件配制
 */
return[
//允许上传的文件mime类型
    'mimes' => [],
    //上传的文件大小限制 (0-不做限制)
    'maxsize' => 0,
    //允许上传的文件后缀
    'exts' => [],
    //自动子目录保存文件
    'autosub' => true,
    //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
    'subname' => ['date', 'y-m-d'],
    //保存根路径
    'rootpath' => dirname($_SERVER['SCRIPT_FILENAME']) . DS . 'attached' . DS,
    //保存目录
    'savepath' => '',
    //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
    'savename' => ['uniqid', ''],
    //文件保存后缀，空则使用原后缀
    'saveext' => '',
    //存在同名是否覆盖
    'replace' => false,
    //是否生成hash编码
    'hash' => true,
    //检测文件是否存在回调，如果存在返回文件信息数组
    'callback' => false,
    // 文件上传驱动
    'driver' => 'local',
    // 上传驱动配置
    'driverconfig' => [],
];
