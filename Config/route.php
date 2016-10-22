<?php

/**
 * 路由配制
 */
return [
    //默认项目
    'project' => 'Frontend',
    //默认控制器名
    'controller' => 'Index',
    //默认操作名
    'action' => 'index',
    //兼容PATH_INFO获取
    'pathinfofetch' => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
    //pathinfo分隔符
    'pathinfodepr' => '/',
    //子域名模式
    'domain' => false,
    //域名绑定项目
    'domainlist'=>[
        
    ]
];
