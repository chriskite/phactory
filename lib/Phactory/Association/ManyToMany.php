<?php

class Phactory_Association_ManyToMany extends Phactory_Association_ManyToOne {
    protected $_join_table;
    protected $_from_join_column;
    protected $_to_join_column;

    public function __construct($to_table, $join_table, $from_column = null, $from_join_column = null, $to_join_column = null, $to_column = null) {
        parent::__construct($to_table, $from_column, $to_column);
        $this->_join_table = $join_table;
        $this->_from_join_column = $from_join_column;
        $this->_to_join_column = $to_join_column;
    }

    public function setFromTable($table) {
        $this->_from_table = $table;
        $this->_guessFromColumn();
        $this->_guessFromJoinColumn();
        $this->_guessToJoinColumn();
        $this->_guessToColumn();
    }

    public function getJoinTable() {
        return $this->_join_table;
    }

    public function setJoinTable($table) {
        $this->_join_table = $table;
    }

    public function getFromJoinColumn() {
        return $this->_from_join_column;
    }

    public function setFromJoinColumn($column) {
        $this->_from_join_column = $column;
    }

    public function getToJoinColumn() {
        return $this->_to_join_column;
    }

    public function setToJoinColumn($column) {
        $this->_to_join_column = $column;
    }

    protected function _guessFromColumn() {
        if(null == $this->_from_column) {
            $guess = $this->_from_table->getPrimaryKey();
            $this->setFromColumn($guess);
        }
    }

    protected function _guessFromJoinColumn() {
        if(null === $this->_from_join_column) {
            $guess = $this->_from_table->getSingularName() . '_id';
            if($this->_join_table->hasColumn($guess)) {
                $this->setFromJoinColumn($guess);
            } else {
                throw new Exception("Unable to guess from_join_column and none specified");
            }
        }
    }

    protected function _guessToJoinColumn() {
        if(null === $this->_to_join_column) {
            $guess = $this->_to_table->getSingularName() . '_id';
            if($this->_join_table->hasColumn($guess)) {
                $this->setToJoinColumn($guess);
            } else {
                throw new Exception("Unable to guess from_join_column and none specified");
            }
        }
    }

    protected function _guessToColumn() {
        if(null == $this->_to_column) {
            $guess = $this->_to_table->getPrimaryKey();
            $this->setToColumn($guess);
        }
    }
}
