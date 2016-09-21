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
 * Sqlite数据库
 * @author CookPHP <admin@cookphp.org>
 */
class Sqlite extends Database {

    protected $startQuote = '"';
    protected $endQuote = '"';
    protected $_queryStats = [];
    protected $baseConfig = [
        'persistent' => true,
        'database' => null,
    ];
    protected $commands = [
        'begin' => 'BEGIN TRANSACTION',
        'commit' => 'COMMIT TRANSACTION',
        'rollback' => 'ROLLBACK TRANSACTION',
    ];

    public function enabled() {
        return extension_loaded('sqlite');
    }

    public function connect() {
        if (!$this->config['persistent']) {
            $this->connection = sqlite_open($this->config['database']);
        } else {
            $this->connection = sqlite_popen($this->config['database']);
        }
        $this->connected = is_resource($this->connection);

        if ($this->connected) {
            $this->query('PRAGMA count_changes = 1;');
        }

        if (!empty($this->config['charset'])) {
            $this->setCharset($this->config['charset']);
        }

        if (!empty($this->config['timezone'])) {
            $this->setTimezone($this->config['timezone']);
        }

        return $this->connection;
    }

    public function disConnect() {
        $this->connected = !sqlite_close($this->connection);
        return !$this->connected;
    }

    public function query($sql) {
        if (preg_match('/^(INSERT|UPDATE|DELETE)/', $sql)) {
            list($this->_queryStats) = $this->fetch($sql);
            return $this->result;
        }
        $this->result = $this->logSql((string) $sql, function () use ($sql) {
            return sqlite_query($this->connection, $sql);
        });
        return $this->result;
    }

    public function fetch($sql) {
        $data = [];
        $this->result = $this->logSql((string) $sql, function () use ($sql) {
            return sqlite_query($this->connection, $sql);
        });

        while ($row = sqlite_fetch_array($this->result, SQLITE_ASSOC)) {
            $data[] = $row;
        }

        return $data;
    }

    public function lastError() {
        $error = sqlite_last_error($this->connection);
        if ($error) {
            return $error . ': ' . sqlite_error_string($error);
        }
    }

    public function lastInsertId() {
        return sqlite_last_insert_rowid($this->connection);
    }

    public function lastAffected() {
        if (!empty($this->_queryStats)) {
            foreach (['rows inserted', 'rows updated', 'rows deleted'] as $key) {
                if (array_key_exists($key, $this->_queryStats)) {
                    return $this->_queryStats[$key];
                }
            }
        }

        return false;
    }

    public function lastNumRows() {
        if ($this->hasResult()) {
            sqlite_num_rows($this->result);
        }

        return false;
    }

    public function limit($limit, $offset = null) {
        if ($limit) {
            $rt = '';
            if (!strpos(strtolower($limit), 'limit') || strpos(strtolower($limit), 'limit') === 0) {
                $rt = ' LIMIT';
            }
            $rt .= ' ' . $limit;
            if ($offset) {
                $rt .= ' OFFSET ' . $offset;
            }

            return $rt;
        }
    }

    public function setCharset($enc) {
        if (!in_array($enc, ['UTF-8', 'UTF-16', 'UTF-16le', 'UTF-16be'])) {
            return false;
        }

        return $this->query('PRAGMA encoding =' . $this->value($enc)) !== false;
    }

    public function getCharset() {
        return $this->fetchRow('PRAGMA encoding');
    }

    public function renderStatement($type, $data) {
        switch (strtolower($type)) {
            case 'schema':
                foreach (['columns', 'indexes'] as $var) {
                    if (is_array($data[$var])) {
                        $data[$var] = "\t" . implode(",\n\t", array_filter($data[$var]));
                    }
                }
                $sql = 'CREATE TABLE ' . $data['table'] . " (\n" . $data['columns'] . ");\n{" . $data['indexes'] . '}';
                break;
            default:
                $sql = parent::renderStatement($type, $data);
                break;
        }
        return $sql;
    }

    public function getTables() {
        $result = $this->fetch("SELECT name FROM sqlite_master WHERE type='table' "
                . "UNION ALL SELECT name FROM sqlite_temp_master "
                . "WHERE type='table' ORDER BY name");
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
        if (function_exists('sqlite_escape_string')) {
            $str = sqlite_escape_string(trim($str));
        } else {
            $str = addslashes(trim($str));
        }
        return $str;
    }

}
