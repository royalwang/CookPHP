<?php

/**
 * CookPHP framework
 *
 * @name CookPHP framework
 * @package CookPHP
 * @author CookPHP <admin@cookphp.org>
 * @version 1.0 Beta
 * @link http://www.cookphp.org
 * @copyright cookphp.org
 * @license <a href="http://www.cookphp.org">CookPHP</a>
 */
return[
    'invalid_connection_str' => '无法根据提交的连接字符串确定数据库设置。  ',
    'unable_to_connect' => '无法使用提供的设置连接到数据库服务器。',
    'unable_to_select' => '无法选择指定的数据库： %s',
    'unable_to_create' => '无法创建指定的数据库：%s',
    'invalid_query' => '提交的查询无效。',
    'must_set_table' => '查询中必须设置要查询的表名。',
    'must_use_set' => '更新数据请使用 Set 方法。',
    'must_use_index' => '必须指定索引以匹配批量更新。',
    'batch_missing_index' => '批量更新操作中一个或多个行缺少指定的索引。',
    'must_use_where' => '更新操作必须包含 Where 条件。',
    'del_must_use_where' => '删除操作必须包含 Where 或 Like 条件。',
    'field_param_missing' => '获取字段需要指定表名。',
    'unsupported_function' => '功能不被您当前使用的数据库支持。',
    'transaction_failure' => '事务失败：执行回滚。',
    'unable_to_drop' => '无法删除指定的数据库。',
    'unsupported_feature' => '特性不被您当前使用的数据库支持。  ',
    'unsupported_compression' => '您选择的文件压缩格式不被服务器支持。  ',
    'filepath_error' => '提交的文件路径无法写入。  ',
    'invalid_cache_path' => '提交的缓存路径无效或无法写入。',
    'table_name_required' => '操作需要指定表名。 ',
    'column_name_required' => '操作需要指定列名。  ',
    'column_definition_required' => '操作需要指定列定义。',
    'unable_to_set_charset' => '无法设置字符集： %s',
    'error_heading' => '数据库发生错误。'
];
