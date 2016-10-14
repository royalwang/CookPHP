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
 * Mysqli数据库
 * @author CookPHP <admin@cookphp.org>
 */
class Mysqli extends Database {

    protected $startQuote = '`';
    protected $endQuote = '`';
    protected $attempts = 0;
    protected $baseConfig = [
        'persistent' => true,
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'logics',
        'port' => '3306',
        'charset' => 'UTF8',
        'timezone' => '',
    ];
    protected $updateSql = 'UPDATE %TABLE%%ALIASES% SET %SET% %WHERE%%LIMIT%';

    public function enabled() {
        return extension_loaded('mysqli');
    }

    public function connect() {
        $this->connected = false;
        if (is_numeric($this->config['port'])) {
            $this->config['socket'] = null;
        } else {
            $this->config['socket'] = $this->config['port'];
            $this->config['port'] = null;
        }
        $this->connection = mysqli_connect($this->config['host'], $this->config['username'], $this->config['password'], $this->config['database'], $this->config['port'], $this->config['socket']);
        if ($this->connection !== false) {
            $this->connected = true;
            $this->attempts = 0;
        }
        if (!empty($this->config['charset'])) {
            $this->setCharset($this->config['charset']);
        }
        if (!empty($this->config['timezone'])) {
            $this->setTimezone($this->config['timezone']);
        }

        $this->setAnsiQuotes();

        return $this->connection;
    }

    public function disConnect() {
        if (isset($this->results) && is_resource($this->results)) {
            mysqli_free_result($this->results);
        }
        $this->connected = !mysqli_close($this->connection);

        return !$this->connected;
    }

    public function query($sql) {
        $result = $this->logSql((string) $sql, function () use ($sql) {
            return mysqli_query($this->connection, $sql);
        });
        if (!$result) {
            $messages = [
                'MySQL server has gone away',
                'php_network_getaddresses: getaddrinfo failed:',
            ];

            $connect = false;
            $error = $this->lastError();

            foreach ($messages as $message) {
                if (strpos($error, $message) !== false) {
                    $connect = true;
                }
            }

            if ($connect && $this->attempts <= 3) {
                ++$this->attempts;
                $this->disconnect();
                $this->connect();

                return $this->query($sql);
            }
        }

        return $result;
    }

    public function fetch($sql) {
        $this->result = $this->query($sql);

        if (!$this->result) {
            return [];
        }

        $rows = [];
        while ($row = mysqli_fetch_assoc($this->result)) {
            $rows[] = $row;
        }

        mysqli_free_result($this->result);

        return $rows;
    }

    public function lastError() {
        if ($this->connection && mysqli_errno($this->connection)) {
            return mysqli_errno($this->connection) . ': ' . mysqli_error($this->connection);
        }
    }

    public function lastInsertId() {
        return mysqli_insert_id($this->connection);
    }

    public function lastAffected() {
        if ($this->result) {
            return mysqli_affected_rows($this->connection);
        }
    }

    public function lastNumRows() {
        if ($this->result) {
            return mysqli_num_rows($this->result);
        }
    }

    private function setCharset($enc) {
        return $this->query('SET NAMES ' . $this->value($enc)) !== false;
    }

    public function getCharset() {
        return mysqli_client_encoding($this->connection);
    }

    private function setTimezone($zone) {
        return $this->query('SET time_zone = ' . $this->value($zone)) !== false;
    }

    private function setAnsiQuotes() {
        return $this->query('SET SQL_MODE=ANSI_QUOTES') !== false;
    }

    public function renderStatement($type, $data) {
        $aliases = null;
        $alias = $data['alias'];
        switch (strtolower($type)) {
            case 'update':
                if (!empty($alias)) {
                    $aliases = "{$this->alias}" . $data['alias'] . $data['joins'] . ' ';
                }
                $sql = str_replace(['%TABLE%', '%ALIASES%', '%SET%', '%WHERE%', '%LIMIT%'], [$data['table'], $aliases, $data['values'], $data['conditions'] ?? '', $data['limit'] ?? ''], $this->updateSql);

                break;
            default:
                $sql = parent::renderStatement($type, $data);
                break;
        }
        return $sql;
    }

    public function getTables() {
        $result = $this->fetch('SHOW TABLES');
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
        if (function_exists('mysqli_real_escape_string')) {
            $str = mysqli_real_escape_string($this->connection, trim($str));
        } else {
            $str = addslashes(trim($str));
        }
        return $str;
    }

}
