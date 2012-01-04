<?php

namespace Phactory\Sql\DbUtil;

use Phactory\Sql\Phactory;

class MysqlUtil extends AbstractDbUtil
{
    protected $_quoteChar = '`';

    public function getPrimaryKey($table) {
        $table = $this->quoteIdentifier($table);
        $stmt = $this->_pdo->query("SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'");
        $result = $stmt->fetch();
        return $result['Column_name'];
    }

    public function getColumns($table) {
        $table = $this->quoteIdentifier($table);
        $stmt = $this->_pdo->query("DESCRIBE $table");
        $columns = array();
        while($row = $stmt->fetch()) {
            $columns[] = $row['Field'];
        }
        return $columns;
    }

    public function disableForeignKeys() {
        $this->_pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    }

    public function enableForeignKeys() {
        $this->_pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    }
}
