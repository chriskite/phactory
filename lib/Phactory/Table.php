<?

class Phactory_Table {
    protected $_singular;
    protected $_name;
    protected $_db_util;

    public function __construct($singular_name) {
        $this->_db_util = Phactory_DbUtilFactory::getDbUtil();
        $this->_singular = $singular_name;
        $this->_name = Phactory_Inflector::pluralize($singular_name);
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
}
