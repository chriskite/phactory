<?php

class Phactory_Association_ManyToOne {
    protected $_from_table;
    protected $_to_table;
    protected $_from_column;
    protected $_to_column;

    public function __construct($to_table, $from_column = null, $to_column = null) {
        $this->setToColumn($to_column);
        $this->setFromColumn($from_column);
        $this->setToTable($to_table);
    }

    public function getFromTable() {
        return $this->_from_table;
    }

    public function setFromTable($table) {
        $this->_from_table = $table;
        $this->_guessFromColumn();
    }

    public function getToTable() {
        return $this->_to_table;
    }

    public function setToTable($table) {
        $this->_to_table = $table;
        $this->_guessToColumn();
    }

    public function getFromColumn() {
        return $this->_from_column;
    }

    public function setFromColumn($column) {
        $this->_from_column = $column;
    }

    public function getToColumn() {
        return $this->_to_column;
    }

    public function setToColumn($column) {
        $this->_to_column = $column;
    }

    protected function _guessFromColumn() {
        if(null === $this->_from_column) {
            $guess = $this->_to_table->getSingularName() . '_id';
            if($this->_from_table->hasColumn($guess)) {
                $this->setFromColumn($guess);
            } else {
                throw new Exception("Unable to guess from_column for association and none specified");
            }
        }
    }

    protected function _guessToColumn() {
        if(null === $this->_to_column) {
            $this->_to_column = $this->_to_table->getPrimaryKey();
            if(!$this->_to_column) {
                throw new Exception("Unable to determine primary key for table '{$this->_to_table}' and none specified");
            }
        }
    }
}
