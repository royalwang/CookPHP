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

namespace Drivers\Database;

use \Core\Database;

/**
 * Mssql数据库
 * @author CookPHP <admin@cookphp.org>
 */
class Mssql extends Database {

    protected $lastError = false;
    protected $startQuote = '[';
    protected $endQuote = ']';
    protected $baseConfig = [
        'persistent' => true,
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'logics',
        'port' => '1433',
    ];
    protected $commands = [
        'begin' => 'BEGIN TRANSACTION',
        'commit' => 'COMMIT',
        'rollback' => 'ROLLBACK',
    ];
    protected $createTableSql = "CREATE TABLE %TABLE% (\n\t%COLUMNS%\n);\n%INDEXES%";
    protected $selectSql = 'SELECT %LIMIT%%FIELD% FROM %TABLE%%ALIASES%%JOIN%%WHERE%%GROUP%%ORDER%';
    protected $selectSql2 = 'SELECT * FROM (SELECT %LIMIT% * FROM (SELECT TOP %OFFSET%%FIELD% FROM %TABLE%%ALIASES%%JOIN%%WHERE%%GROUP%%ORDER%) AS Set1 %RORDER%) AS Set2 %ORDER2%';

    public function enabled() {
        return extension_loaded('mssql');
    }

    public function connect() {
        $os = env('OS');
        if (!empty($os) && strpos($os, 'Windows') !== false) {
            $sep = ',';
        } else {
            $sep = ':';
        }
        $this->connected = false;

        if (is_numeric($this->config['port'])) {
            $port = $sep . $this->config['port'];    // Port number
        } elseif ($this->config['port'] === null) {
            $port = '';                        // No port - SQL Server 2005
        } else {
            $port = '\\' . $this->config['port'];    // Named pipe
        }

        if (!$this->config['persistent']) {
            $this->connection = mssql_connect($this->config['host'] . $port, $this->config['username'], $this->config['password'], true);
        } else {
            $this->connection = mssql_pconnect($this->config['host'] . $port, $this->config['username'], $this->config['password']);
        }

        if (mssql_select_db($this->config['database'], $this->connection)) {
            $this->qery('SET DATEFORMAT ymd');
            $this->connected = true;
        }

        return $this->connection;
    }

    public function disConnect() {
        mssql_free_result($this->results);
        $this->connected = !mssql_close($this->connection);

        return !$this->connected;
    }

    public function query($sql) {
        $result = $this->logSql((string) $sql, function () use ($sql) {
            return mssql_query($sql, $this->connection);
        });
        $this->lastError = ($result === false);
        return $result;
    }

    public function fetch($sql) {
        $this->result = $this->query($sql);

        $data = [];
        while ($row = mssql_fetch_array($this->result)) {
            $data[] = $row;
        }

        return $data;
    }

    public function lastError() {
        if ($this->lastError) {
            $error = mssql_get_last_message();
            if ($error && !preg_match('/contexto de la base de datos a|contesto di database|changed database|contexte de la base de don|datenbankkontext/i', $error)) {
                return $error;
            }
        }
    }

    public function lastInsertId() {
        return mssql_result(mysql_query('select SCOPE_IDENTITY()', $this->connection), 0, 0);
    }

    public function lastAffected() {
        if ($this->result) {
            return mssql_rows_affected($this->connection);
        }
    }

    public function lastNumRows() {
        if ($this->result) {
            return mssql_num_rows($this->result);
        }
    }

    public function limit($limit, $offset = null) {
        if ($limit) {
            $rt = '';
            if (!strpos(strtolower($limit), 'top') || strpos(strtolower($limit), 'top') === 0) {
                $rt = ' TOP';
            }
            $rt .= ' ' . $limit;
            if (is_int($offset) && $offset > 0) {
                $rt .= ' OFFSET ' . $offset;
            }

            return $rt;
        }
    }

    public function renderStatement($type, $data) {
        switch (strtolower($type)) {
            case 'select':
                extract($data);
                $fields = trim($fields);
                if (strpos($limit, 'TOP') !== false && strpos($fields, 'DISTINCT ') === 0) {
                    $limit = 'DISTINCT ' . trim($limit);
                    $fields = substr($fields, 9);
                }
                if (preg_match('/offset\s+([0-9]+)/i', $limit, $offset)) {
                    $limit = preg_replace('/\s*offset.*$/i', '', $limit);
                    preg_match('/top\s+([0-9]+)/i', $limit, $limitVal);
                    $offset = intval($offset[1]) + intval($limitVal[1]);
                    $rOrder = $this->switchSort($order);
                    list($order2, $rOrder) = [$this->mapFields($order), $this->mapFields($rOrder)];
                    $sql = str_replace(['%LIMIT%', '%OFFSET%', '%FIELD%', '%TABLE%', '%ALIASES%', '%JOIN%', '%WHERE%', '%GROUP%', '%ORDER%', '%RORDER%', '%ORDER2%'], [$limit ?? '', $offset ?? '', $fields ?? '', $table ?? '', $alias ?? '', $joins ?? '', $conditions ?? '', $group ?? '', $order ?? '', $rOrder ?? '', $order2 ?? ''], $this->selectSql2);
                } else {
                    $sql = str_replace(['%LIMIT%', '%FIELD%', '%TABLE%', '%ALIASES%', '%JOIN%', '%WHERE%', '%GROUP%', '%ORDER%'], [$limit ?? '', $fields ?? '', $table ?? '', $alias ?? '', $joins ?? '', $conditions ?? '', $group ?? '', $order ?? ''], $this->selectSql);
                }
                break;
            case 'schema':
                extract($data);
                $indexes = $data['indexes'];
                $columns = $data['columns'];
                foreach ($indexes as $i => $index) {
                    if (preg_match('/PRIMARY KEY/', $index)) {
                        unset($indexes[$i]);
                        break;
                    }
                }
                foreach (['columns', 'indexes'] as $var) {
                    if (is_array(${$var})) {
                        ${$var} = "\t" . implode(",\n\t", array_filter(${$var}));
                    }
                }
                $sql = str_replace(['%TABLE%', '%COLUMNS%', '%INDEXES%'], [$data['table'], $columns, $indexes], $this->createTableSql);
                break;
            default:
                $sql = parent::renderStatement($type, $data);
                break;
        }
        return $sql;
    }

    private function switchSort($order) {
        $order = preg_replace('/\s+ASC/i', '__tmp_asc__', $order);
        $order = preg_replace('/\s+DESC/i', ' ASC', $order);
        return preg_replace('/__tmp_asc__/', ' DESC', $order);
    }

    private function mapFields($sql) {
        if (empty($sql) || empty($this->__fieldMappings)) {
            return $sql;
        }
        foreach ($this->__fieldMappings as $key => $val) {
            $sql = preg_replace('/' . preg_quote($val) . '/', $this->name($key), $sql);
            $sql = preg_replace('/' . preg_quote($this->name($val)) . '/', $this->name($key), $sql);
        }

        return $sql;
    }

    public function getTables() {
        $result = $this->fetch("SELECT TABLE_NAME
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_TYPE = 'BASE TABLE'
            ");
        $info = [];
        foreach ($result as $key => $val) {
            $info[$key] = current($val);
        }
        return $info;
    }

    public function escape($str) {
        if ($str == '') {
            return '';
        }
        if (function_exists('mssql_real_escape_string')) {
            $str = mssql_real_escape_string(trim($str));
        } else {
            $str = addslashes(trim($str));
        }
        return $str;
    }

}
