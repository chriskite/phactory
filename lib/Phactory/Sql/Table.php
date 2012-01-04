<?php

namespace Phactory\Sql;

class Table {
    protected $_singular;
    protected $_name;
    protected $_db_util;
    protected $_phactory;

    public function __construct($singular_name, $pluralize = true, Phactory $phactory) {
        $this->_phactory = $phactory;
        $this->_db_util = DbUtilFactory::getDbUtil($phactory);
        $this->_singular = $singular_name;
        if($pluralize) {
            $this->_name = Inflector::pluralize($singular_name);
        } else {
            $this->_name = $singular_name;
        }
    }

    public function getName() {
        return $this->_name;
    }

    public function getSingularName() {
        return $this->_singular;
    }

    public function getPrimaryKey() {
        return $this->_db_util->getPrimaryKey($this->_name);
    }

    public function hasColumn($column) {
        return in_array($column, $this->getColumns());
    }

    public function getColumns() {
       return $this->_db_util->getColumns($this->_name);
    }

    public function __toString() {
        return $this->_name;
    }

    /**
     * Added by Artūrs Gailītis, to support database-specific quote characters
     *
     * @param string $identifier - table/column name to be quoted in a proper
     *        way for the database driver, table is using.
     * @return string quoted identifier
     */
    public function quoteIdentifier($identifier)
    {
        return $this->_db_util->quoteIdentifier($identifier);
    }

}
