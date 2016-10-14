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
 * Pgsql数据库
 * @author CookPHP <admin@cookphp.org>
 */
class Pgsql extends Database {

    protected $startQuote = '"';
    protected $endQuote = '"';
    protected $baseConfig = [
        'persistent' => true,
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'logics',
        'schema' => 'public',
        'port' => 5432,
        'charset' => '',
    ];
    protected $commands = [
        'begin' => 'BEGIN',
        'commit' => 'COMMIT',
        'rollback' => 'ROLLBACK',
    ];
    protected $createTableSql = "CREATE TABLE %TABLE% (\n\t%COLUMNS%\n);\n%INDEXES%";

    public function enabled() {
        return extension_loaded('pgsql');
    }

    public function connect() {
        $conn = "host='{$this->config['host']}' port='{$this->config['port']}' dbname='{$this->config['database']}' ";
        $conn .= "user='{$this->config['username']}' password='{$this->config['password']}'";

        if (!$this->config['persistent']) {
            $this->connection = pg_connect($conn, PGSQL_CONNECT_FORCE_NEW);
        } else {
            $this->connection = pg_pconnect($conn);
        }
        $this->connected = false;

        if ($this->connection) {
            $this->connected = true;
            $this->query('SET search_path TO ' . $this->config['schema']);
        }
        if (!empty($this->config['charset'])) {
            $this->setCharset($this->config['charset']);
        }

        return $this->connection;
    }

    public function disConnect() {
        if ($this->result) {
            pg_free_result($this->result);
        }
        if (is_resource($this->connection)) {
            $this->connected = !pg_close($this->connection);
        } else {
            $this->connected = false;
        }

        return !$this->connected;
    }

    public function query($sql) {
        return $this->logSql((string) $sql, function () use ($sql) {
                    return pg_query($this->connection, $sql);
                });
    }

    public function fetch($sql) {
        $this->result = $this->query($sql);
        $data = pg_fetch_all($this->result);
        return $data;
    }

    public function lastError() {
        $error = pg_last_error($this->connection);
        return ($error) ? $error : null;
    }

    public function lastInsertId() {
        return pg_last_oid($this->connection);
    }

    public function lastAffected() {
        return ($this->result) ? pg_affected_rows($this->result) : false;
    }

    public function lastNumRows() {
        return ($this->result) ? pg_num_rows($this->result) : false;
    }

    public function setCharset($enc) {
        return $this->query('SET NAMES ' . $this->value($enc)) !== false;
    }

    public function getCharset() {
        return pg_client_encoding($this->connection);
    }

    public function limit($limit, $offset = null, $page = null) {
        if ($limit) {
            $rt = '';
            if (!strpos(strtolower($limit), 'limit') || strpos(strtolower($limit), 'limit') === 0) {
                $rt = ' LIMIT';
            }

            $rt .= ' ' . $limit;
            if (intval($page) && !$offset) {
                $offset = $limit * ($page - 1);
            }

            if ($offset) {
                $rt .= ' OFFSET ' . $offset;
            }

            return $rt;
        }
    }

    public function renderStatement($type, $data) {
        switch (strtolower($type)) {
            case 'schema':
                extract($data);
                foreach ($indexes as $i => $index) {
                    if (preg_match('/PRIMARY KEY/', $index)) {
                        unset($indexes[$i]);
                        $columns[] = $index;
                        break;
                    }
                }
                $join = ['columns' => ",\n\t", 'indexes' => "\n"];
                foreach (['columns', 'indexes'] as $var) {
                    if (is_array(${$var})) {
                        ${$var} = implode($join[$var], array_filter(${$var}));
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

    public function getTables() {
        $result = $this->fetch("select tablename as Tables_in_test from pg_tables where  schemaname ='" . $this->config['schema'] . "'");
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
        if (function_exists('pg_escape_string')) {
            $str = pg_escape_string(trim($str));
        } else {
            $str = addslashes(trim($str));
        }
        return $str;
    }

}
