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
    'invalid_connection_str' => 'Unable to determine the database settings based on the connection string you submitted.',
    'unable_to_connect' => 'Unable to connect to your database server using the provided settings.',
    'unable_to_select' => 'Unable to select the specified database: %s',
    'unable_to_create' => 'Unable to create the specified database: %s',
    'invalid_query' => 'The query you submitted is not valid.',
    'must_set_table' => 'You must set the database table to be used with your query.',
    'must_use_set' => 'You must use the "set" method to update an entry.',
    'must_use_index' => 'You must specify an index to match on for batch updates.',
    'batch_missing_index' => 'One or more rows submitted for batch updating is missing the specified index.',
    'must_use_where' => 'Updates are not allowed unless they contain a "where" clause.',
    'del_must_use_where' => 'Deletes are not allowed unless they contain a "where" or "like" clause.',
    'field_param_missing' => 'To fetch fields requires the name of the table as a parameter.',
    'unsupported_function' => 'This feature is not available for the database you are using.',
    'transaction_failure' => 'Transaction failure: Rollback performed.',
    'unable_to_drop' => 'Unable to drop the specified database.',
    'unsupported_feature' => 'Unsupported feature of the database platform you are using.',
    'unsupported_compression' => 'The file compression format you chose is not supported by your server.',
    'filepath_error' => 'Unable to write data to the file path you have submitted.',
    'invalid_cache_path' => 'The cache path you submitted is not valid or writable.',
    'table_name_required' => 'A table name is required for that operation.',
    'column_name_required' => 'A column name is required for that operation.',
    'column_definition_required' => 'A column definition is required for that operation.',
    'unable_to_set_charset' => 'Unable to set client connection character set: %s',
    'error_heading' => 'A Database Error Occurred'
];
