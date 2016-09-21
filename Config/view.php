<?php

/*
 * 模板视图设置
 */
return[
    //模板驱动 为空时使用框架自带引擎
    'driver' => '',
    //编译目录
    'compiledir' => __CACHE__ . 'ViewCompile',
    //缓存目录
    'cachedir' => __TMP__ . 'ViewCache',
    // 是否开启模板编译缓存,设为false则每次都会重新编译
    'compilecache' => true,
    //压缩html空格
    'compresshtml'=>true,
    //驱动配制
    'config' => [
    ]
];

