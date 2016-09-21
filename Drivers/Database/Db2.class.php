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

namespace Drivers\Database;

use \Core\Database;

/**
 * Db2数据库
 * @author CookPHP <admin@cookphp.org>
 */
class Db2 extends Database {

    protected $startQuote = '';
    protected $endQuote = '';
    protected $baseConfig = [
        'persistent' => true,
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'logics',
        'schema' => 'public',
        'port' => 50000,
        'charset' => '',
    ];
    protected $createTableSql = "CREATE TABLE %TABLE% (\n\t%COLUMNS%\n);\n%INDEXES%";

    public function enabled() {
        return extension_loaded('ibm_db2');
    }

    public function connect() {
        $conn = "DATABASE='{$this->config['database']}';HOSTNAME='{$this->config['host']}';PORT={$this->config['port']};";
        $conn .= "PROTOCOL=TCPIP;UID={$this->config['username']};PWD={$this->config['password']};";

        if (!$this->config['persistent']) {
            $this->connection = db2_connect($conn, PGSQL_CONNECT_FORCE_NEW);
        } else {
            $this->connection = db2_pconnect($conn);
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
            db2_free_result($this->result);
        }
        if (is_resource($this->connection)) {
            $this->connected = !db2_close($this->connection);
        } else {
            $this->connected = false;
        }

        return !$this->connected;
    }

    public function query($sql) {
        $stmt = $this->logSql((string) $sql, function () use ($sql) {
            return db2_prepare($this->connection, $sql);
        });
        $res = db2_execute($stmt);
        return $res;
    }

    public function fetch($sql) {
        $data = [];
        $stmt = $this->logSql((string) $sql, function () use ($sql) {
            return db2_prepare($this->connection, $sql);
        });
        $this->result = db2_execute($stmt);

        while ($row = db2_fetch_assoc($stmt)) {
            $data[] = $row;
        }

        return $data;
    }

    public function lastError() {
        $error = db2_conn_errormsg($this->connection);

        return ($error) ? $error : null;
    }

    public function lastInsertId() {
        return db2_last_lastInsertId($this->connection);
    }

    public function lastAffected() {
        return ($this->result) ? db2_num_rows($this->result) : false;
    }

    public function lastNumRows() {
        return ($this->result) ? db2_num_rows($this->result) : false;
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
                $indexes = $data['indexes'];
                $columns = $data['columns'];
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
        $result = $this->fetch("SELECT NAME FROM SYSIBM.SYSTABLES WHERE TYPE='T' AND CREATOR='DB2ADMIN'");
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
        if (function_exists('db2_escape_string')) {
            $str = db2_escape_string(trim($str));
        } else {
            $str = addslashes(trim($str));
        }
        return $str;
    }

}
