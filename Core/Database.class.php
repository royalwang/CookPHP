<?php

/*
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
 * 数据库驱动抽象
 * @author CookPHP <admin@cookphp.org>
 */
abstract class Database {

    /**
     * 字段转义符号开始
     * @var string
     */
    protected $startQuote = '`';

    /**
     * 字段转义符号结束
     * @var string
     */
    protected $endQuote = '`';

    /**
     * index definition, primary, index, unique.
     *
     * @var array
     */
    //protected $index = ['PRI' => 'primary', 'MUL' => 'index', 'UNI' => 'unique'];

    /**
     * 别名分配符
     * @var string
     */
    protected $alias = ' AS ';

    /**
     * SQL事务命令集合
     * @var array
     */
    protected $commands = [
        'begin' => 'BEGIN',
        'commit' => 'COMMIT',
        'rollback' => 'ROLLBACK',
    ];
    protected $connection = false;

    /**
     * 连接数据源
     * @var bool
     */
    protected $connected = false;

    /**
     * 默认配置
     * @var array
     */
    protected $baseConfig = [];

    /**
     * 数据源配置
     * @var array
     */
    protected $config = [];
    protected $transaction = false;
    protected $result = null;
    protected $selectSql = 'SELECT %FIELD% FROM %TABLE%%ALIASES%%JOIN%%WHERE%%GROUP%%ORDER%%LIMIT%';
    protected $insertSql = '%INSERT% INTO %TABLE% (%FIELD%) VALUES (%DATA%)';
    protected $updateSql = 'UPDATE %TABLE%%ALIASES% SET %SET% %WHERE%';
    protected $deleteSql = 'DELETE %ALIAS% FROM %TABLE% %ALIASES%%WHERE%%LIMIT%';
    protected $createTableSql = "CREATE TABLE %TABLE% (\n%COLUMNS%%INDEXES%)";

    /**
     * 检查驱动是可用
     * @access public
     * @return bool 
     */
    public function enabled() {
        return true;
    }

    /**
     * 连接到数据库
     * @access public
     * @return bool 
     */
    abstract public function connect();

    /**
     * 断开连接的数据库
     * @access public
     * @return bool 
     */
    abstract public function disConnect();

    /**
     * 查询SQL语句并返回数组集
     * @access public
     * @param string $sql
     * @return resource
     */
    abstract public function fetch($sql);

    /**
     * 返回数据库错误信息
     * @access public
     * @return string
     */
    abstract public function lastError();

    /**
     * 执行的SQL语句
     * @access public
     * @param string $sql 
     * @return resource
     */
    abstract public function query($sql);

    /**
     * 返回上次INSERT操作生成的ID
     * @access public
     * @param mixed $source
     * @return mixed 
     */
    abstract public function lastInsertId();

    /**
     * 返回上次操作所影响的记录行数
     * @access public
     * @param mixed $source
     * @return int
     */
    abstract public function lastAffected();

    /**
     * 返回结果集中行的数目
     * @access public
     * @param mixed $source
     * @return int
     */
    abstract public function lastNumRows();

    /**
     * 取得数据库的表信息
     * @access public
     * @access public
     * @return array
     */
    abstract function getTables();

    /**
     * 安全处理传入值
     * @access public
     * @return string
     * */
    abstract public function escape($str);

    /**
     * 设置数据库配置
     * @access public
     * @param array $config
     */
    public function setConfig(array $config = []) {
        $this->config = array_merge($this->baseConfig, $this->config, $config);
    }

    /**
     * 返回数据库配置
     * @access public
     * @param array $config
     */
    public function getConfig(): array {
        return $this->config;
    }

    /**
     * 生成并从数组生成SQL语句
     * @access public
     * @param array  $query
     * @param object $model 
     * @return string
     * */
    public function buildStatement(&$query, $table = '', $type = 'select') {
        //$query = @array_merge(['offset' => null, 'joins' => []], $query);
        if (!empty($query['joins'])) {
            $count = count($query['joins']);
            for ($i = 0; $i < $count; ++$i) {
                if (is_array($query['joins'][$i])) {
                    $query['joins'][$i] = $this->buildJoinStatement($query['joins'][$i]);
                }
            }
        }
        return $this->renderStatement($type, [
                    'replace' => $query['replace'] ?? false,
                    'conditions' => $this->conditions($query['conditions'] ?? '', true, true),
                    'fields' => $this->fields($query['fields'] ?? ''),
                    'values' => isset($query['values']) ? implode(', ', $query['values']) : '',
                    'table' => $this->table($query['table'] ?? $table),
                    'alias' => !empty($query['alias']) ? $this->alias . $this->name($query['alias']) : '',
                    'order' => isset($query['order']) ? $this->order($query['order']) : '',
                    'limit' => $this->limit($query['limit'] ?? '', $query['offset'] ?? '', $query['page'] ?? ''),
                    'joins' => isset($query['joins']) ? implode(' ', $query['joins']) : '',
                    'group' => isset($query['group']) ? $this->group($query['group']) : '',
        ]);
    }

    /**
     * buildStatement别名
     * @access public
     * @return string
     * */
    public function buildQuery($query, $table = '', $type = 'select') {
        return $this->buildStatement($query, $table, $type);
    }

    /**
     * 渲染一个最终的SQL JOIN语句
     * @access public
     * @param array $data
     * @return string
     */
    public function renderJoinStatement($data) {
        return trim($data['type'] . ' JOIN ' . $data['table'] . ' ' . $data['alias'] . ' ON (' . $data['conditions'] . ')');
    }

    /**
     * 生成并从数组生成一个JOIN语句
     * @access public
     * @param array $join
     * @return string
     */
    public function buildJoinStatement($join) {
        $data = array_merge([
            'type' => null,
            'alias' => null,
            'table' => 'join_table',
            'conditions' => [],
                ], $join);

        if (!empty($data['alias'])) {
            $data['alias'] = $this->alias . $this->name($data['alias']);
        }
        if (!empty($data['conditions'])) {
            $data['conditions'] = trim($this->conditions($data['conditions'], true, false));
        }

        return $this->renderJoinStatement($data);
    }

    /**
     * 呈现最终正确的顺序的SQL语句
     * @access public
     * @param string $type
     * @param array  $data
     * @return string
     */
    public function renderStatement($type, $data) {
        $aliases = null;
        $alias = $data['alias'];
        switch (strtolower($type)) {
            case 'select':
                $sql = str_replace(['%FIELD%', '%TABLE%', '%ALIASES%', '%JOIN%', '%WHERE%', '%GROUP%', '%ORDER%', '%LIMIT%'], [$data['fields'] ?? '*', $data['table'], $data['alias'] ?? '', $data['joins'] ?? '', $data['conditions'] ?? '', $data['group'] ?? '', $data['order'] ?? '', $data['limit'] ?? ''], $this->selectSql);
                break;
            case 'create':
            case 'insert':
                $values = $data['values'];
                $values = rtrim($values, ')');
                $values = ltrim($values, '(');
                $sql = str_replace(['%INSERT%', '%TABLE%', '%FIELD%', '%DATA%'], [$data['replace'] ? 'REPLACE' : 'INSERT', $data['table'], $data['fields'], $values,], $this->insertSql);
                break;
            case 'update':
                if (!empty($alias)) {
                    $aliases = "{$this->alias}" . $data['alias'] . $data['joins'] . ' ';
                }
                $sql = str_replace(['%TABLE%', '%ALIASES%', '%SET%', '%WHERE%'], [$data['table'], $aliases, $data['values'], $data['conditions'] ?? ''], $this->updateSql);
                break;
            case 'delete':
                if (!empty($alias)) {
                    $aliases = "{$this->alias}" . $data['alias'] . $data['joins'] . ' ';
                }
                $sql = str_replace(['%ALIAS%', '%TABLE%', '%ALIASES%', '%WHERE%', '%LIMIT%'], [$data['alias'] ?? '', $data['table'] ?? '', $aliases ?? '', $data['conditions'] ?? '', $data['limit'] ?? ''], $this->deleteSql);
                break;
            case 'schema':
                foreach (['columns', 'indexes'] as $var) {
                    if (is_array($data[$var])) {
                        $data[$var] = "\t" . implode(",\n\t", array_filter($data[$var]));
                    }
                }
                $indexes = $data['indexes'];
                $columns = $data['columns'];
                if (trim($indexes) != '') {
                    $columns .= ',';
                }
                $sql = str_replace(['%TABLE%', '%COLUMNS%', '%INDEXES%'], [$data['table'], $columns, $indexes], $this->createTableSql);
                break;
        }
        return $sql;
    }

    /**
     * 通过解析条件
     * @access public
     * @param mixed $conditions  数组或条件字符串
     * @param bool  $quoteValues
     * @param bool  $where
     * @param Model $model 
     * @return string 
     */
    public function conditions($conditions, $quoteValues = true, $where = true, $model = null) {
        $clause = $out = '';
        if ($where) {
            $clause = ' WHERE ';
        }
        if (is_array($conditions) && !empty($conditions)) {
            $out = $this->conditionKeysToString($conditions, $quoteValues, $model);
            if (empty($out)) {
                return $clause . ' 1 = 1';
            }
            return $clause . implode(' AND ', $out);
        }
        if ($conditions === false || $conditions === true) {
            return $clause . (int) $conditions . ' = 1';
        }
        if (empty($conditions) || trim($conditions) == '') {
            return $clause . '1 = 1';
        }
        $clauses = '/^WHERE\\x20|^GROUP\\x20BY\\x20|^HAVING\\x20|^ORDER\\x20BY\\x20/i';
        if (preg_match($clauses, $conditions)) {
            $clause = '';
        }
        if (trim($conditions) == '') {
            $conditions = ' 1 = 1';
        } else {
            $conditions = $this->quoteFields($conditions);
        }
        return $clause . $conditions;
    }

    /**
     * 通过解析给定条件阵创建一个WHERE
     * @access public
     * @param array $conditions
     * @param bool  $quoteValues
     * @return string
     */
    public function conditionKeysToString($conditions, $quoteValues = true) {
        $out = [];
        $data = null;
        $bool = ['and', 'or', 'not', 'and not', 'or not', 'xor', '||', '&&'];

        foreach ($conditions as $key => $value) {
            $join = ' AND ';
            $not = null;

            if (is_array($value)) {
                $valueInsert = (
                        !empty($value) &&
                        (substr_count($key, '?') == count($value) || substr_count($key, ':') == count($value))
                        );
            }

            if (is_numeric($key) && empty($value)) {
                continue;
            } elseif (is_numeric($key) && is_string($value)) {
                $out[] = $not . $this->quoteFields($value);
            } elseif ((is_numeric($key) && is_array($value)) || in_array(strtolower(trim($key)), $bool)) {
                if (in_array(strtolower(trim($key)), $bool)) {
                    $join = ' ' . strtoupper($key) . ' ';
                } else {
                    $key = $join;
                }
                $value = $this->conditionKeysToString($value, $quoteValues);

                if (strpos($join, 'NOT') !== false) {
                    if (strtoupper(trim($key)) == 'NOT') {
                        $key = 'AND ' . trim($key);
                    }
                    $not = 'NOT ';
                }

                if (empty($value[1])) {
                    if ($not) {
                        $out[] = $not . '(' . $value[0] . ')';
                    } else {
                        $out[] = $value[0];
                    }
                } else {
                    $out[] = '(' . $not . '(' . implode(') ' . strtoupper($key) . ' (', $value) . '))';
                }
            } else {
                $data = $this->parseKey(trim($key), $value);

                if ($data != null) {
                    if (preg_match('/^\(\(\((.+)\)\)\)$/', $data)) {
                        $data = substr($data, 1, strlen($data) - 2);
                    }
                    $out[] = $data;
                    $data = null;
                }
            }
        }

        return $out;
    }

    /**
     * SQL条件运
     * @access public
     * @param string $key
     * @param mixed  $value
     * @return string
     */
    protected function parseKey($key, $value) {
        preg_match('/(#?)([\w\.\-\|&]+)(\[(\>|\>\=|\<|\<\=|\!|\`|\<\>|\>\<|exp|\!?~)\])?/i', $key, $match);
        if (strpos($match[2], '|')) {
            $array = explode('|', $match[2]);
            $str = [];
            foreach ($array as $k) {
                $str[] = $this->parseKey($k . ($match[3] ?? ''), $value);
            }
            $operator = '( ' . implode(' OR ', $str) . ' )';
        } elseif (strpos($match[2], '&')) {
            $array = explode('&', $match[2]);
            $str = [];
            foreach ($array as $k) {
                $str[] = $this->parseKey($k . ($match[3] ?? ''), $value);
            }
            $operator = '( ' . implode(' AND ', $str) . ' )';
        } else {
            $type = gettype($value);
            $column = $this->name($match[2]);
            if (isset($match[4])) {
                $operator = $match[4];
                if ($operator == 'exp') {
                    $operator = $column . ' REGEXP ' . $this->value($value);
                } elseif ($operator == '!') {
                    switch ($type) {
                        case 'null':
                            $operator = $column . ' IS NOT NULL';
                            break;
                        case 'array':
                            $operator = $column . ' NOT IN (' . $this->value($value) . ')';
                            break;
                        case 'integer':
                        case 'double':
                            $operator = $column . ' != ' . $value;
                            break;
                        case 'boolean':
                            $operator = $column . ' != ' . ($value ? '1' : '0');
                            break;
                        case 'string':
                            $operator = $column . ' != ' . $this->value($value);
                            break;
                    }
                } elseif ($operator == '<>' || $operator == '><') {
                    if ($type == 'array') {
                        if ($operator == '><') {
                            $column .= ' NOT';
                        }
                        if (is_numeric($value[0]) && is_numeric($value[1])) {
                            $operator = '(' . $column . ' BETWEEN ' . $value[0] . ' AND ' . $value[1] . ')';
                        } else {
                            $operator = '(' . $column . ' BETWEEN ' . $this->value($value[0]) . ' AND ' . $this->value($value[1]) . ')';
                        }
                    }
                } elseif ($operator == '~' || $operator == '!~') {
                    if ($type != 'array') {
                        $value = (array) $value;
                    }
                    $likeClauses = [];
                    foreach ($value as $item) {
                        $item = strval($item);
                        if (preg_match('/^(?!(%|\[|_])).+(?<!(%|\]|_))$/', $item)) {
                            $item = '%' . $item . '%';
                        }
                        $likeClauses[] = $column . ($operator === '!~' ? ' NOT' : '') . ' LIKE ' . $this->value($item);
                    }
                    $operator = implode(' OR ', $likeClauses);
                } elseif (in_array($operator, array('>', '>=', '<', '<='))) {
                    if (is_numeric($value)) {
                        $operator = $column . ' ' . $operator . ' ' . $value;
                    } else {
                        $operator = $column . ' ' . $operator . ' ' . $this->value($value);
                    }
                }
            } else {
                switch ($type) {
                    case 'null':
                        $operator = $column . ' IS NULL';
                        break;
                    case 'array':
                        $operator = $column . ' IN (' . $this->value($value) . ')';
                        break;
                    case 'integer':
                    case 'double':
                        $operator = $column . ' = ' . $value;
                        break;
                    case 'boolean':
                        $operator = $column . ' = ' . ($value ? 1 : 0);
                        break;
                    case 'string':
                        $operator = $column . ' = ' . $this->value($value);
                        break;
                }
            }
        }
        return $operator;
    }

    /**
     * 处理查询字段名称
     * @access public
     * @param string $conditions
     * @return string|false
     */
    protected function quoteFields($conditions) {
        $start = $end = null;
        $original = $conditions;

        if (!empty($this->startQuote)) {
            $start = preg_quote($this->startQuote);
        }
        if (!empty($this->endQuote)) {
            $end = preg_quote($this->endQuote);
        }
        $conditions = str_replace([$start, $end], '', $conditions);
        preg_match_all('/(?:[\'\"][^\'\"\\\]*(?:\\\.[^\'\"\\\]*)*[\'\"])|([a-z0-9_' . $start . $end . ']*\\.[a-z0-9_' . $start . $end . ']*)/i', $conditions, $replace, PREG_PATTERN_ORDER);

        if (isset($replace['1']['0'])) {
            $pregCount = count($replace['1']);

            for ($i = 0; $i < $pregCount; ++$i) {
                if (!empty($replace['1'][$i]) && !is_numeric($replace['1'][$i])) {
                    $conditions = preg_replace('/\b' . preg_quote($replace['1'][$i]) . '\b/', $this->name($replace['1'][$i]), $conditions);
                }
            }

            return $conditions;
        }
        return $original;
    }

    /**
     * 处理LIMIT
     * @access public
     * @param int $limit  返回结果数
     * @param int $offset 开始结果
     * @return string
     */
    public function limit($limit, $offset = null, $page = null) {
        if ($limit) {
            $rt = '';
            if (!stripos($limit, 'limit') || strpos(strtolower($limit), 'limit') === 0) {
                $rt = ' LIMIT';
            }

            if (intval($offset)) {
                $rt .= ' ' . $offset . ',';
            }

            if (intval($page) && !$offset) {
                $rt .= ' ' . $limit * ($page - 1) . ',';
            }

            $rt .= ' ' . $limit;

            return $rt;
        }
    }

    /**
     * table分析
     * @access protected
     * @param mixed $table
     * @return string
     */
    protected function table($tables, $alias = '') {
        if (empty($tables)) {
            return '';
        }
        if (!is_array($tables)) {
            $tables = preg_split('/\s*,\s*/', trim($tables), -1, PREG_SPLIT_NO_EMPTY);
        }
        foreach ($tables as $i => $table) {
            if (strpos($table, '(') === false) {
                if (preg_match('/^(.*?)(?i:\s+as|)\s+([^ ]+)$/', $table, $matches)) {
                    $tables[$i] = $this->name($matches[1]) . ' ' . $this->alias . ' ' . $this->name($matches[2]);
                } else {
                    $tables[$i] = $this->name($table) . ($alias ? ' ' . $this->alias . ' ' . $this->name($alias) : '');
                }
            }
        }
        return implode(',', $tables);
    }

    /**
     * field分析
     * @access protected
     * @param mixed $fields
     * @return string
     */
    protected function fields($fields) {
        if ('*' == $fields || empty($fields)) {
            $fieldsStr = '*';
        } else {
            if (!is_array($fields)) {
                $fields = preg_split('/\s*,\s*/', trim($fields), -1, PREG_SPLIT_NO_EMPTY);
            }
            foreach ($fields as $i => $column) {
                if (strpos($column, '(') === false) {
                    if (preg_match('/^(.*?)\s+AS\s+(\w+)$/im', $column, $match)) {
                        $fields[$i] = (strpos($match[1], '(') !== false ? $match[1] : $this->name($match[1])) . ' ' . $this->alias . ' ' . $this->name($match[2]);
                    } elseif (preg_match('/^(.*?)\s+\.\s+(\w+)$/im', $column, $match)) {
                        $fields[$i] = (strpos($match[1], '(') === false ? $match[1] : $this->name($match[1])) . '.' . ($match[2] == '*' ? $match[2] : $this->name($match[2]));
                    } else {
                        $fields[$i] = $this->name($column);
                    }
                }
            }
            $fieldsStr = implode($fields, ',');
        }
        return $fieldsStr;
    }

    /**
     * 创建ORDER BY
     * @access public
     * @param string $order 
     * @param string $direction  (ASC or DESC)
     * @return string 
     */
    protected function order($order, $direction = 'ASC') {
        if (empty($order)) {
            return '';
        }
        if (!is_array($order)) {
            $order = preg_split('/\s*,\s*/', trim($order), -1, PREG_SPLIT_NO_EMPTY);
        }

        foreach ($order as $i => $column) {
            if (strpos($column, '(') === false) {
                if (preg_match('/^(.*?)\s+(asc|desc)$/i', $column, $matches)) {
                    $order[$i] = $this->name($matches[1]) . ' ' . strtoupper($matches[2]);
                } else {
                    $order[$i] = $this->name($column) . ' ' . $direction;
                }
            }
        }
        $order = implode(', ', $order);
        return ' ORDER BY ' . $order;
    }

    /**
     * 创建GROUP BY SQL
     * @access public
     * @param string $group
     * @return mixed string
     */
    protected function group($group) {
        if (!empty($group)) {
            if (is_array($group)) {
                $group = implode(', ', $group);
            }
            return ' GROUP BY ' . $this->quoteFields($group);
        }
    }

    /**
     * 转义数据库查询值
     * @access public
     * @param mixed  $data
     * @return mixed
     */
    public function value($data) {
        if (is_array($data) && !empty($data)) {
            return implode(',', array_map([$this, 'value'], $data));
        }
        if (is_numeric($data)) {
            return $data;
        } elseif (is_null($data)) {
            return "''";
        } else {
            return "'" . $this->escape($data) . "'";
        }
    }

    /**
     * 转义字段名称
     * 简单的散列算法
     * @access public
     * @param string $data
     * @return string
     */
    public function name($data) {
        if ($data == '*') {
            return '*';
        }
        if (is_object($data) && isset($data->type)) {
            return $data->value;
        }
        $array = is_array($data);
        $data = (array) $data;
        $count = count($data);

        for ($i = 0; $i < $count; ++$i) {
            if ($data[$i] == '*') {
                continue;
            }
            if (strpos($data[$i], '(') !== false && preg_match_all('/([^(]*)\((.*)\)(.*)/', $data[$i], $fields)) {
                $fe = [];
                foreach ($fields as $field) {
                    $fe[] = $field[0];
                }

                $fields = $fe;

                //$fields = Set::extract($fields, '{n}.0');

                if (!empty($fields[1])) {
                    if (!empty($fields[2])) {
                        $data[$i] = $fields[1] . '(' . $this->name($fields[2]) . ')' . $fields[3];
                    } else {
                        $data[$i] = $fields[1] . '()' . $fields[3];
                    }
                }
            }
            $data[$i] = str_replace('.', $this->endQuote . '.' . $this->startQuote, $data[$i]);
            $data[$i] = $this->startQuote . $data[$i] . $this->endQuote;
            $data[$i] = str_replace($this->startQuote . $this->startQuote, $this->startQuote, $data[$i]);
            $data[$i] = str_replace($this->startQuote . '(', '(', $data[$i]);
            $data[$i] = str_replace(')' . $this->startQuote, ')', $data[$i]);
            $alias = !empty($this->alias) ? $this->alias : 'AS ';

            if (preg_match('/\s+' . $alias . '\s*/', $data[$i])) {
                if (preg_match('/\w+\s+' . $alias . '\s*/', $data[$i])) {
                    $quoted = $this->endQuote . ' ' . $alias . $this->startQuote;
                    $data[$i] = str_replace(' ' . $alias, $quoted, $data[$i]);
                } else {
                    $quoted = $alias . $this->startQuote;
                    $data[$i] = str_replace($alias, $quoted, $data[$i]) . $this->endQuote;
                }
            }

            if (!empty($this->endQuote) && $this->endQuote == $this->startQuote) {
                if (substr_count($data[$i], $this->endQuote) % 2 == 1) {
                    if (substr($data[$i], -2) == $this->endQuote . $this->endQuote) {
                        $data[$i] = substr($data[$i], 0, -1);
                    } else {
                        $data[$i] = trim($data[$i], $this->endQuote);
                    }
                }
            }
            if (strpos($data[$i], '*')) {
                $data[$i] = str_replace($this->endQuote . '*' . $this->endQuote, '*', $data[$i]);
            }
            $data[$i] = str_replace($this->endQuote . $this->endQuote, $this->endQuote, $data[$i]);
        }

        return (!$array) ? $data[0] : $data;
    }

    /**
     * 转义字段名称
     * @access public
     * @param mixed
     * @return string
     */
    public function names($data) {
        if (is_object($data) && isset($data->type)) {
            return $data->value;
        }
        if ($data === '*') {
            return '*';
        }
        if (is_array($data)) {
            foreach ($data as $i => $dataItem) {
                $data[$i] = $this->name($dataItem);
            }

            return $data;
        }
        $data = trim($data);
        if (preg_match('/^[\w-]+(?:\.[^ \*]*)*$/', $data)) {
            if (strpos($data, '.') === false) {
                return $this->startQuote . $data . $this->endQuote;
            }
            $items = explode('.', $data);

            return implode($this->endQuote . '.' . $this->startQuote, $items) . $this->endQuote;
        }
        if (preg_match('/^[\w-]+\.\*$/', $data)) {
            return $this->startQuote . str_replace('.*', $this->endQuote . '.*', $data);
        }
        if (preg_match('/^([\w-]+)\((.*)\)$/', $data, $matches)) {
            return $matches[1] . '(' . $this->name($matches[2]) . ')';
        }
        if (preg_match('/^([\w-]+(\.[\w-]+|\(.*\))*)\s+' . preg_quote($this->alias) . '\s*([\w-]+)$/i', $data, $matches)) {
            return preg_replace('/\s{2,}/', ' ', $this->name($matches[1]) . ' ' . $this->alias . ' ' . $this->name($matches[3]));
        }
        if (preg_match('/^[\w-_\s]*[\w-_]+/', $data)) {
            return $this->startQuote . $data . $this->endQuote;
        }

        return $data;
    }

    /**
     * 准备字段和值SQL UPDATE
     * @param array $fields
     * @param bool  $quoteValues
     * @param bool  $alias
     * @return array
     */
    protected function prepareUpdateFields($fields, $quoteValues = true, $alias = false) {
        $quotedAlias = $this->startQuote . $this->endQuote;

        $updates = [];
        foreach ($fields as $field => $value) {
            if ($alias && strpos($field, '.') === false) {
                $quoted = $model->escapeField($field);
            } elseif (!$alias && strpos($field, '.') !== false) {
                $quoted = $this->name(str_replace($quotedAlias . '.', '', str_replace(
                                        $model->alias . '.', '', $field
                )));
            } else {
                $quoted = $this->name($field);
            }

            if ($value === null) {
                $updates[] = $quoted . ' = NULL';
                continue;
            }
            $update = $quoted . ' = ';

            if ($quoteValues) {
                $update .= $this->value($value);
            } elseif (!$alias) {
                $update .= str_replace($quotedAlias . '.', '', str_replace(
                                $model->alias . '.', '', $value
                ));
            } else {
                $update .= $value;
            }
            $updates[] = $update;
        }

        return $updates;
    }

    /**
     * 格式创建表
     * @param array  $indexes
     * @param string $table
     * @return array
     */
    public function buildIndex($indexes) {
        $join = [];
        foreach ($indexes as $name => $value) {
            $out = '';
            if ($name === 'PRIMARY') {
                $out .= 'PRIMARY ';
                $name = null;
            } else {
                if (!empty($value['unique'])) {
                    $out .= 'UNIQUE ';
                }
                $name = $this->startQuote . $name . $this->endQuote;
            }
            if (is_array($value['column'])) {
                $out .= 'KEY ' . $name . ' (' . implode(', ', array_map([&$this, 'name'], $value['column'])) . ')';
            } else {
                $out .= 'KEY ' . $name . ' (' . $this->name($value['column']) . ')';
            }
            $join[] = $out;
        }

        return $join;
    }

    /**
     * 返回alias
     * @return string
     */
    public function getAlias() {
        return $this->alias;
    }

    /**
     * 清空表
     * @access public
     * @param string $table 要靖空的表
     * @return bool
     */
    public function truncate($table) {
        return $this->query('TRUNCATE TABLE ' . $table);
    }

    /**
     * 统计数组
     * @param array $array
     * @param bool  $all
     * @param int   $count
     * @return int
     * @static
     */
    protected function countDim($array = null, $all = false, $count = 0): int {
        if ($all) {
            $depth = [$count];
            if (is_array($array) && reset($array) !== false) {
                foreach ($array as $value) {
                    $depth[] = $this->countDim($value, true, $count + 1);
                }
            }
            $return = max($depth);
        } else {
            if (is_array(reset($array))) {
                $return = $this->countDim(reset($array)) + 1;
            } else {
                $return = 1;
            }
        }

        return $return;
    }

    /**
     * 开始事务
     * @access public
     * @return bool
     */
    public function begin(): bool {
        if ($this->query($this->commands['begin'])) {
            $this->transaction = true;
            return true;
        }
        return false;
    }

    /**
     * 提交事务
     * @access public
     * @return bool
     */
    public function commit(): bool {
        if ($this->query($this->commands['commit'])) {
            $this->transaction = false;
            return true;
        }
        return false;
    }

    /**
     * 回滚事务
     * @access public
     * @return bool
     */
    public function rollback(): bool {
        if ($this->query($this->commands['rollback'])) {
            $this->transaction = false;
            return true;
        }
        return false;
    }

    protected function logSqlError($sql) {
        $message = $this->lastError() . ' : ' . $sql;
        //Log::write('sql', $message);
        return true;
    }

    /**
     * 返回最后一个SQl
     * @access public
     * @return array
     */
    public function lastSql(): array {
        return Log::lastLog('sql');
    }

    /**
     * 返回执行Sql
     * @access public
     * @return array
     */
    public function getSql(): array {
        return Log::getLog('sql');
    }

    /**
     * 创建SQL查询日志
     * @access public
     * @param string $sql
     * @param \closure $callable
     * @return $callable
     */
    public function logSql($sql, \closure $callable) {
        return Log::setLog('sql', $sql, $callable);
    }

    /**
     * 关闭连接
     * @access public
     * @return bool
     */
    public function close(): bool {
        return $this->connected = false;
    }

    /**
     * 关闭当前的数据源
     * @access public
     */
    public function __destruct() {
        if ($this->transaction) {
            $this->rollback();
        }
        if ($this->connected) {
            $this->close();
        }
//        if ($this->lastError()) {
//            $this->logSqlError();
//        }
    }

}
