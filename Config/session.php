<?php

/*
 * Session配制
 */
return[
    //是否默认启动Session
    'start' => true,
    //使用的存储 session 的驱动
    //File Redis
    'driver' => 'Redis',
    //session cookie 的名称
    'name' => 'cook_session',
    //sess名称前缀
    'prefix' => '',
    //你希望 session 持续的秒数 如果你希望 session 不过期（直到浏览器关闭），将其设置为 0
    'expiration' => 7200,
    //Session指定存储位置，取决于使用的存储 session 的驱动
    //'path' => '',
    'path' => 'tcp://localhost:6379',
    //读取 session cookie 时，是否验证用户的 IP 地址 注意有些 ISP 会动态的修改 IP ，所以如果你想要一个不过期的 session，将其设置为 FALSE
    'ip' => true,
];
