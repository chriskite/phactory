<?

class Phactory_Association {
    protected $_collection;

    public function __construct($collection_name) {
        $this->_collection = new Phactory_Collection($collection_name);
    }

    public function getCollection() {
        return $this->_collection;
    }
}
