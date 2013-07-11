<?php

namespace Phactory\Sql;

class Row {
    protected $_table;
    protected $_storage = array();
    protected $_phactory;

    public function __construct($table, $data, Phactory $phactory) {
        $this->_phactory = $phactory;
        if(!$table instanceof Table) {
            $table = new Table($table, true, $phactory);
        }
        $this->_table = $table;
        foreach($data as $key => $value) {
            $this->_storage[$key] = $value;
        }
    }

    public function getId() {
        $pk = $this->_table->getPrimaryKey();
        return $this->_storage[$pk];
    }

    public function save() {
        $pdo = $this->_phactory->getConnection();
        $sql = "INSERT INTO `{$this->_table}` (";

        $data = array();
        $params = array();
        foreach($this->_storage as $key => $value) {
            $index = $this->_table->quoteIdentifier($key);
            $data[$index] = ":$key";
            $params[":$key"] = $value;
        }

        $keys = array_keys($data);
        $values = array_values($data);

        $sql .= join(',', $keys);
        $sql .= ") VALUES (";
        $sql .= join(',', $values);
        $sql .= ")";

        $stmt = $pdo->prepare($sql);
        $r = $stmt->execute($params);

        if($r === false){
            $error= $stmt->errorInfo();
            Logger::error('SQL statement failed: '.$sql.' ERROR MESSAGE: '.$error[2].' ERROR CODE: '.$error[1]);
        }

        // only works if table's primary key autoincrements
        $id = $pdo->lastInsertId();

        if($pk = $this->_table->getPrimaryKey()) {
            if($id){
                $this->_storage[$pk] = $id;
            }else{
                // if key doesn't autoincrement, find last inserted row and set the primary key.
                $sql = "SELECT * FROM `{$this->_table}` WHERE";

                for($i = 0, $size = sizeof($keys); $i < $size; ++$i){
                    $sql .= " {$keys[$i]} = {$values[$i]} AND";
                }

                $sql = substr($sql, 0, -4);

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);

                $this->_storage[$pk] = $result[$pk];
            }
        }

        return $r;
    }

    public function toArray() {
        return $copy = $this->_storage;
    }

    public function __get($key) {
        return $this->_storage[$key];
    }

    public function __set($key, $value) {
        $this->_storage[$key] = $value;
    }

    public function fill() {
        $columns = $this->_table->getColumns();
        foreach ($columns as $column) {
            if ( ! isset($this->_storage[$column]) ) {
               $this->_storage[$column] = null;
            }
        }
        return $this;
    }

    public function __isset($name){
      return(isset($this->_storage[$name]));
    }
}
