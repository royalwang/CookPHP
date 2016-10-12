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

namespace Core;

/**
 * 数据库类
 * @author CookPHP <admin@cookphp.org>
 */
class Model extends Common {

    protected $cache = false;
    protected $connected = false;
    protected $config = [];
    protected $prefix;
    protected $engine;
    protected $charset;
    protected $table;
    protected $driver;
    protected $params;
    protected $data;
    private static $_driver = [];

    public function __construct($table = null, $config = []) {
        $this->setConfig($config)->setTable($table)->connect();
        return $this;
    }

    /**
     * 设置配制
     * @access public
     * @param array $config
     * @param $this
     */
    public function setConfig($config = []) {
        $config = !empty($config) ? array_merge(Config::all('database'), $config) : Config::all('database');
        $this->config = !empty($this->config) ? array_merge($this->config, $config) : $config;
        $this->cache = $this->config['cache'] ?? false;
        $this->prefix = $this->config['prefix'] ?? '';
        $this->engine = $this->config['engine'] ?? '';
        $this->charset = $this->config['charset'] ?? '';
        return $this;
    }

    /**
     * 设置表
     * @access public
     * @param string $table
     * @return $this
     */
    public function setTable($table) {
        if (empty($table)) {
            $name = substr(get_class($this), strlen('Model'));
            if (($pos = strpos($name, '\\')) !== false) {
                $table = substr($name, $pos + 1);
            } else {
                $table = $name;
            }
        }
        $this->table = Route::parseName($table);
        $this->table();
        return $this;
    }

    /**
     * 返回数据库配制
     * @access public
     * @return array
     */
    public function getConfig(): array {
        return $this->config;
    }

    /**
     * 连接数据库驱动程序
     * 或不配制时默认 mysqli
     * @access public
     * @return Drivers\Database
     */
    public function connect() {
        $driver = $this->config['driver'] ?? 'mysqli';
        if (!isset(self::$_driver[$driver])) {
            self::$_driver[$driver] = Loader::initialize('\\Drivers\\Database\\' . ucfirst($driver));
            self::$_driver[$driver]->setConfig($this->config);
            !self::$_driver[$driver]->enabled() && Error::show('Error Database Handler:' . ucfirst($driver), 500);
            $this->connected = self::$_driver[$driver]->connect();
        }
        $this->driver = self::$_driver[$driver] ?? Error::show('Error Database Handler:' . ucfirst($driver), 500);
        return $this->driver;
    }

    /**
     * 检查是否连接了制作成功
     * @access public
     * @return bool
     */
    public function isConnected(): bool {
        if ($this->connected === false) {
            return false;
        }
        $this->connected = $this->driver->query('SELECT 1');
        return $this->connected;
    }

    /**
     * 驱动断开
     * @access public
     */
    public function disConnect() {
        $this->driver->disConnect();
        $this->connected = false;
    }

    /**
     * 重新连接
     * @access public
     * @return bool
     */
    public function reConnect() {
        if (!$this->connected) {
            return false;
        }
        $connected = $this->fetchFromDb('SELECT 1');
        if ($connected) {
            return true;
        }
        $this->disconnect();
        return $this->connect();
    }

    /**
     * 设置fields
     * 一般返回查询字段
     * @access public
     * @param mixed $string
     * @return $this
     */
    public function fields($string = null) {
        $this->params['fields'] = $string ?: '*';
        return $this;
    }

    /**
     * 设置table
     * 查询表
     * @access public
     * @param string $string
     * @return $this
     */
    public function table(string $string = '') {
        $this->params['table'] = $string ?: $this->prefix . $this->table;
        return $this;
    }

    /**
     * 返回表名称
     * @access public
     * @return string
     */
    public function getTableName(): string {
        if (empty($this->params['table'])) {
            $this->table();
        }
        empty($this->params['table']) && Error::show(Lang::get('db.table_name_required'), 500);
        return $this->params['table'];
    }

    /**
     * 设置alias
     * 表别名
     * @access public
     * @param string $string
     * @return $this
     */
    public function alias(string $string) {
        $this->params['alias'] = $string;
        return $this;
    }

    /**
     * 设置order
     * 结果集进行排序 ASC|DESC
     * @access public
     * @param string $string
     * @return $this
     */
    public function order(string $string) {
        $this->params['order'] = $string;
        return $this;
    }

    /**
     * 设置limit
     * 返回指定的记录数
     * @access public
     * @param int $int
     * @return $this
     */
    public function limit(int $int) {
        $this->params['limit'] = $int;
        return $this;
    }

    /**
     * 设置offset
     * 查询结果中以第0条记录为基准（包括第0条）
     * @access public
     * @param int $int
     * @return $this
     */
    public function offset(int $int) {
        $this->params['offset'] = $int;
        return $this;
    }

    /**
     * 设置page
     * 查询页码
     * @access public
     * @param int $int
     * @return $this
     */
    public function page(int $int) {
        $this->params['page'] = $int;
        return $this;
    }

    /**
     * 设置join
     * 关系查询
     * @access public
     * @param array $array
     * @return $this
     */
    public function join(...$join) {
        array_map(function ($var) {
            foreach ((array) $var as $_join) {
                if (!empty($_join)) {
                    $_join = preg_replace_callback("/__([A-Z0-9_-]+)__/sU", function ($match) {
                        return $this->driver->name($this->prefix . strtolower($match[1]));
                    }, $_join);
                    $this->params['joins'][] = false !== stripos($_join, 'JOIN') ? ' ' . $_join : ' INNER JOIN ' . $_join;
                }
            }
        }, $join);
        return $this;
    }

    /**
     * 处理字段名称
     * @param string $name
     * @return string
     */
    public function name($name) {
        return $this->driver->name($name);
    }

    /**
     * 处理字段值
     * @param mixed $value
     * @return string
     */
    public function value($value) {
        return $this->driver->value($value);
    }

    /**
     * 设置group
     * 用于结合合计函数，根据一个或多个列对结果集进行分组
     * @access public
     * @param string|array  $string
     * @return $this
     */
    public function group($string) {
        $this->params['group'] = $string;
        return $this;
    }

    /**
     * 设置values
     * 用于 INSERT、UPDATE
     * @access public
     * @param array  $array
     * @return $this
     */
    public function values(array $array) {
        $this->data($array);
        return $this;
    }

    /**
     * 设置data
     * 用于 INSERT、UPDATE
     * @access public
     * @param array  $array
     * @return $this
     */
    public function data(array $array) {
        $this->data = $array;
        return $this;
    }

    /**
     * 设置缓存
     * @access public
     * @param bool  $bool
     * @return $this
     */
    public function sqlCache(bool $bool) {
        $this->params['cache'] = $bool;
        return $this;
    }

    /**
     * 设置缓存名称
     * 默认为SQL语句
     * @access public
     * @param string  $string
     * @return $this
     */
    public function setSqlName(string $string) {
        $this->params['cache_name'] = $string;
        return $this;
    }

    /**
     * 设置where
     * 设置查询条件
     * @access public
     * @param array|string  $array
     * @return $this
     */
    public function where(...$array) {
        array_map(function ($var) {
            if (!empty($var)) {
                $this->conditions($var);
            }
        }, $array);
        return $this;
    }

    /**
     * 设置where
     * 设置查询条件
     * @access public
     * @param array|string  $array
     * @return $this
     */
    public function conditions(...$array) {
        array_map(function ($var) {
            if (!empty($var)) {
                $this->params['conditions'][] = $var;
            }
        }, $array);
        return $this;
    }

    /**
     * 开始事务
     * @access public
     * @return bool
     */
    public function begin(): bool {
        return $this->driver->begin();
    }

    /**
     * 提交事务
     * @access public
     * @return bool
     */
    public function commit(): bool {
        return $this->driver->commit();
    }

    /**
     * 回滚事务
     * @access public
     * @return bool
     */
    public function rollback(): bool {
        return $this->driver->rollback();
    }

    /**
     * 执行查询
     * 执行INSERT，UPDATE，DELETE语句
     * @access public
     * @param stirng $sql
     * @return
     */
    public function query(string $sql) {
        $sql = $this->strSql($sql);
        //preg_match("/^(insert|delete|update|replace|drop|create)\s+/i", $query)
        //preg_match("/^(insert|replace)\s+/i", $query)
        $this->params = [];
        return $this->driver->query($sql);
    }

    /**
     * 返回最后一个数据库错误
     * @access public
     * @return string
     */
    public function lastError(): string {
        return $this->driver->lastError();
    }

    /**
     * 返回最后一个SQl
     * @access public
     * @return array
     */
    public function lastSql(): array {
        return $this->driver->lastSql();
    }

    /**
     * 返回执行Sql
     * @access public
     * @return array
     */
    public function getSql(): array {
        return $this->driver->getSql();
    }

    /**
     * 返回最后插入行的ID或序列值
     * INSERT产生的最后ID键
     * @access public
     * @return mixed
     */
    public function lastInsertId(): int {
        return $this->driver->lastInsertId();
    }

    /**
     * 返回结果集中行的数目
     * @access public
     * @return int
     */
    public function lastNumRows(): int {
        return $this->driver->lastNumRows();
    }

    /**
     * 返回上次操作所影响的记录行数
     * @access public
     * @return int
     */
    public function lastAffected(): int {
        return $this->driver->lastAffected();
    }

    /**
     * 替换SQl字符表达
     * @param string $string
     * @return string
     */
    public function strSql($string) {
        $string = str_replace('#__PREFIX__#', $this->prefix, $string);
        $string = str_replace('#__ENGINE__#', $this->engine, $string);
        $string = str_replace('#__CHARSET__#', $this->charset, $string);
        return trim($string);
    }

    /**
     * SQL查询
     * @access public
     * @param string     $sql 完整的SQL
     * @param int|string $duration  缓存时间   null时不缓存
     * @param string     缓存名称，默认 md5($sql)
     * @return array
     */
    public function fetch($sql = null, $duration = '', $name = null): array {
        $sql = $this->strSql($sql ?: $this->driver->buildStatement($this->params, $this->getTableName()));
        if ($this->cache === false || $duration === null) {
            return $this->fetchFromDb($sql);
        }
        if ($duration === '') {
            $duration = $this->config['cacheexpire'];
        }
        if (is_string($duration)) {
            switch ($duration) {
                case 'y'://1年
                    $duration = 31536000;
                    break;
                case 'w'://1周
                    $duration = 604800;
                    break;
                case 'd'://1天
                    $duration = 86400;
                    break;
                case 'h'://1小时
                    $duration = 3600;
                    break;
                default:
                    $duration = 0;
            }
        }
        $key = $name ?: md5($sql);
        $key = 'db_' . $key;
        return $this->cache($this->config['cachedriver'] ?? 'file')->remember((string) $key, function () use ($sql) {
                    return $this->fetchFromDb($sql);
                }, (int) $duration);
    }

    /**
     * 返回所有查询记录
     * @access public
     * @param string     $sql 完整的SQL
     * @param int|string $duration  缓存时间   null时不缓存
     * @param string     缓存名称，默认 md5($sql)
     * @return array
     */
    public function fetchAll($sql = null, $duration = null, $name = null): array {
        return $this->fetch($sql, $duration, $name);
    }

    /**
     * 返回单个记录
     * @access public
     * @param string     $sql 完整的SQL
     * @param int|string $duration  缓存时间   null时不缓存
     * @param string     缓存名称，默认 md5($sql)
     * @return array
     */
    public function fetchAssoc($sql = null, $duration = null, $name = null): array {
        return $this->limit(1)->fetch($sql, $duration, $name)[0] ?? [];
    }

    /**
     * 返回查询结果
     * 直接使用驱动
     * @access public
     * @param string $sql
     * @return array
     */
    protected function fetchFromDb($sql): array {
        $this->params = [];
        return $this->driver->fetch($sql);
    }

    /**
     * 返回数据库的表
     * @access public
     * @return array
     */
    public function getTables(): array {
        return $this->driver->getTables();
    }

    /**
     * 统计查询
     * @access public
     * @param string $table
     * @param array  $params
     * @return mixed
     */
    public function count($table = '', $params = []) {
        return $this->limit(1)->find($table, 'count', $params);
    }

    /**
     * 查询并输出
     * @access public
     * @param string $table
     * @param string $type
     * @param array  $params
     * @return mixed
     */
    public function find($table = '', $type = 'first', $params = []) {
        if (empty($table)) {
            $table = $this->getTableName();
        }
        if (empty($table)) {
            return false;
        }
        $params = !empty($params) ? array_merge($this->params, $params) : $this->params;
        if (is_array($table)) {
            return $this->findTables($table, $type, $params);
        }
        $cache = $params['cache'] ?? '';
        $cache_name = $params['cache_name'] ?? '';

        $params['table'] = $table;
        $params['type'] = $type;
        if (!empty($params['limit']) && $params['limit'] < 0) {
            unset($params['limit']);
        }
        switch ($type) {
            case 'count':
                $params['fields'] = 'COUNT(' . ($params['fields'] ?? '*') . ') ' . $this->driver->getAlias() . $this->driver->name('total');
                unset($params['order'], $params['offset'], $params['page']);
                $query = $this->driver->buildStatement($params, $table);
                if (isset($params['group'])) {
                    $query = 'SELECT COUNT(*) ' . $this->driver->getAlias() . $this->driver->name('total') . ' FROM (' . $query . ') as ' . $this->driver->name('tmp');
                }
                $data = $this->fetch($query, $cache, $cache_name);
                $results = intval($data[0]['total'] ?? 0);
                unset($data);
                break;
            case 'all':
                $query = $this->driver->buildStatement($params, $table);
                $results = $this->fetch($query, $cache, $cache_name);
                break;
            /*
             * array('fields' => array('value','group','title'),
             *       'empty' => array('module','mod_view'),
             *       'replace' => array('parent_id','name')
             *       );
             */
            case 'list':
                $query = $this->driver->buildStatement($params, $table);
                $rows = $this->fetch($query, $cache, $cache_name);

                $fields = [];
                if (is_array($rows[0])) {
                    $fields = array_keys($rows[0]);
                }

                if (count($fields) == 0) {
                    $results = &$rows;
                } else {
                    $empty = $params['empty'];
                    $replace = $params['replace'];

                    $value = $fields[0];
                    $option = $fields[1];
                    $optgrp = $fields[2];
                    $res = [];
                    foreach ($rows as $row) {
                        if (count($fields) == 1) {
                            $res[$row[$value]] = $row[$value];
                        } elseif (count($fields) == 2) {
                            $res[$row[$value]] = $row[$option];
                        } else {
                            if ($empty) {
                                $v = $empty[0];
                                $re = $empty[1];

                                if (!$row[$v] && $option == $re) {
                                    $res[$row[$re]][$row[$value]] = $row[$option];
                                } elseif (!$row[$v] && $optgrp == $re) {
                                    $res[$row[$optgrp]][$row[$value]] = $row[$re];
                                } else {
                                    $res[$row[$optgrp]][$row[$value]] = $row[$option];
                                }
                            } else {
                                $res[$row[$optgrp]][$row[$value]] = $row[$option];
                            }

                            if ($replace) {
                                $res[$row[$option]][$row[$value]] = $row[$optgrp];
                            }
                        }
                    }
                    unset($value, $option, $rows, $optgrp, $v, $re);
                    $results = &$res;
                }
                break;
            case 'neighbors':
            case 'siblings':
                $field = $params['field'];
                $value = $params['value'];
                unset($params['value'], $params['field']);

                if (empty($params['limit'])) {
                    $params['limit'] = 1;
                }
                if (!is_array($params['conditions'])) {
                    $conditions = [];
                } else {
                    $conditions = $params['conditions'];
                }
                $params['order'] = [$field . ' DESC'];
                $params['conditions'] = array_merge([$field . ' < ' => $value], $conditions);

                $query = $this->driver->buildStatement($params, $table);
                $data = $this->fetch($query, $cache, $cache_name);

                $results['prev'] = ($params['limit']) ? $data[0] : $data;

                //build second query
                $params['order'] = [$field];
                $params['conditions'] = array_merge([$field . ' > ' => $value], $conditions);

                $query = $this->driver->buildStatement($params, $table);
                $data = $this->fetch($query, $cache, $cache_name);

                $results['next'] = isset($params['limit']) ? ($data[0] ?? []) : $data;
                break;
            case 'first':
                $params['limit'] = 1;
                $query = $this->driver->buildStatement($params, $table);
                $results = $this->fetch($query, $cache, $cache_name);
                $results = $results[0] ?? [];
                break;
            case 'field':
                $query = $this->driver->buildStatement($params, $table);
                $rows = $this->fetch($query, $cache, $cache_name);
                $results = [];
                foreach ($rows as $key => $v) {
                    $results[$key][] = $v;
                }
                unset($rows);
                break;
        }

        return $results;
    }

    /**
     * 合并查询并输出
     * @access public
     * @param array $tables 表
     * @param string $type
     * @param array  $params
     * @return mixed
     */
    public function findTables($tables = [], $type = 'all', $params = []) {
//        if (empty($table)) {
//            $table = [$this->getTableName()];
//        }
        if ($type == 'count') {
            $total = 0;
            foreach ($tables as $table) {
                $total += $this->find($table, $type, $params);
            }
            return $total;
        }
        if ($type == 'first') {
            foreach ($tables as $table) {
                $rows = $this->find($table, $type, $params);
                if (!empty($rows)) {
                    return $rows;
                }
            }
        }
        if ($type == 'all') {
            $total = 0;
            $data = [];
            $limit = $params['limit'] ?? null;
            foreach ($tables as $table) {
                $rows = $this->find($table, 'all', $params);
                $data = array_merge($data, $rows);
                if ($limit) {
                    $total += count($rows);
                    if ($total >= $limit) {
                        break;
                    } else {
                        $params['limit'] = $limit - $total;
                    }
                }
            }
            unset($rows);
            return $data;
        }
        return $this->find($table, $type, $params);
    }

    /**
     * 新增数据
     * @access public
     * @param bool  $replace 是否replace新增
     * @param string $table 表
     * @param array  $data 数据
     * @return bool
     */
    public function save($replace = false, $table = '', $data = []): bool {
        if (empty($table)) {
            $table = $this->getTableName();
        }
        if (empty($data)) {
            $data = $this->data;
        }
        if (empty($data) || empty($table)) {
            return false;
        }
        $k = $v = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $va = [];
                foreach ($value as $k2 => $v2) {
                    $va[] = ($v2) ? $this->driver->value($v2) : "''";
                    $k[] = $this->driver->name($k2);
                }
                $v[] = '(' . implode(',', $va) . ')';
            } else {
                $v[] = ($value) ? $this->driver->value($value) : "''";
                $k[] = $this->driver->name($key);
            }
        }
        $k = array_unique($k);
        $params = [];
        $params = $this->params;
        $params['table'] = $table;
        $params['replace'] = (bool) $replace;
        $params['fields'] = $k;
        $params['values'] = $v;
        $query = $this->driver->buildStatement($params, $table, 'insert');
        return $this->query($query);
    }

    /**
     * 更新数数据
     * @access public
     * @param string $table 表
     * @param array  $data 数据
     * @param array  $conditions 条件表达
     * @return bool
     */
    public function update($table = '', $data = [], $conditions = [], $details = []): bool {
        if (empty($table)) {
            $table = $this->getTableName();
        }
        if (empty($data)) {
            $data = $this->data;
        }
        if (empty($data) || empty($table)) {
            return false;
        }
        $params = [];
        $params = $this->params;
        $params['table'] = $table;
        $params['fields'] = $data;

        if (!empty($conditions)) {
            $params['conditions'] = !empty($params['conditions']) ? array_merge($params['conditions'], $conditions) : $conditions;
        }

        $k = [];
        foreach ($params['fields'] as $key => $value) {
            //运算支持 +、-、*、/
            preg_match('/([\w]+)(\[(\+|\-|\*|\/)\])?/i', $key, $match);
            if (isset($match[3]) && is_numeric($value)) {
                $k[] = $this->driver->name($match[1]) . ' = ' . $this->driver->name($match[1]) . $match[3] . $value;
            } elseif (isset($value[0]) && 'exp' == $value[0]) {
                $k[] = $this->driver->name($key) . '=' . (string) $value[1];
            } elseif (is_numeric($key)) {
                $k[] = $key . ' = ' . $this->driver->value($value);
            } else {
                $k[] = $this->driver->name($key) . ' = ' . $this->driver->value($value);
            }
        }
        unset($params['fields']);
        $params['values'] = $k;
        $query = $this->driver->buildStatement($params, $params['table'], 'update');
        return $this->query($query);
    }

    /**
     * 自动新增或更新数据
     * 如果数据存在则更新操作
     * @access public
     * @param string $table 表
     * @param array  $conditions 条件表达
     *  @param array  $details limit表达
     * @return bool
     */
    public function cascade($table = '', $data = [], $conditions = [], $details = []) {
        if (empty($table)) {
            $table = $this->getTableName();
        }
        if (empty($data)) {
            $data = $this->data;
        }
        if (empty($conditions)) {
            $conditions = $this->params['conditions'] ?? [];
        }
        $rows = $this->find($table, 'count', [
            'conditions' => $conditions,
        ]);
        if ($rows > 0) {
            return $this->update($table, $data, $conditions, $details);
        }
        if (count(array_filter($data))) {
            return $this->save($table, $data, $details);
        }
    }

    /**
     * 删除数据
     * @access public
     * @param string $table 表
     * @param array  $conditions 条件表达
     *  @param array  $details limit表达
     * @return bool
     */
    public function delete($table = '', $conditions = [], $details = []): bool {
        if (empty($table)) {
            $table = $this->getTableName();
        }
        if (empty($table)) {
            return false;
        }
        $params = [];
        $params = $this->params;
        $params['table'] = $table;
        if (!empty($conditions)) {
            $params['conditions'] = $conditions;
        }
        $query = $this->driver->buildStatement($params, $params['table'], 'delete');
        return $this->query($query);
    }

    public function createTable($table = '', $data = []) {
        if (empty($table)) {
            $table = $this->getTableName();
        }
        if (empty($data)) {
            $data = $this->data;
        }
        $params = [];
        $params['table'] = $table;
        foreach ($data as $key => $value) {
            $params['columns'][] = $key;
            $params['indexes'][] = $value;
        }
        $query = $this->driver->buildStatement($params, $params['table'], 'schema');
        return $this->query($query);
    }

    /**
     * 清空表
     * @access public
     * @param string $table 要靖空的表
     * @return bool
     */
    public function truncate(string $table): bool {
        return $this->driver->truncate($table);
    }

    /**
     * 安全处理传入值
     * @access public
     * @param string $value
     * @return string
     */
    public function escape(string $value): string {
        return $this->driver->escape($value);
    }

    /**
     * 关闭连接
     * @access public
     * @return bool
     */
    public function close(): bool {
        return $this->driver->close();
    }

}
