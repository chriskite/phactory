<?

class Phactory_Table {
    protected $_singular;
    protected $_name;

    public function __construct($singular_name) {
        $this->_singular = $singular_name;
        $inflector = new Inflector();
        $this->_name = $inflector->pluralize($singular_name);
    }

    public function getName() {
        return $this->_name;
    }

    public function getSingularName() {
        return $this->_singular;
    }

    public function getPrimaryKey() {
        $db_util = Phactory_DbUtilFactory::getDbUtil();
        return $db_util->getPrimaryKey($this->_name);
    }

    public function __toString() {
        return $this->_name;
    }
}
