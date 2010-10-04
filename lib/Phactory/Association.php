<?php

class Phactory_Association {
    protected $_to_table;
    protected $_fk_column;

    public function __construct($to_table, $fk_column) {
        $this->_to_table = $to_table;
        $this->_fk_column = $fk_column;
    }

    public function getTable() {
        return $this->_to_table;
    }

    public function getFkColumn() {
        return $this->_fk_column;
    }
}
