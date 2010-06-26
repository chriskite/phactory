<?

class Phactory_Blueprint {
    protected $_table;
    protected $_defaults;

    public function __construct($table, $defaults) {
        $this->_table = $table;
        $this->_defaults = $defaults;
    }

    public function create() {
        return new Phactory_Row($this->_table, $this->_defaults);
    }
}
