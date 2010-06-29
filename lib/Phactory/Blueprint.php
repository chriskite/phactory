<?

class Phactory_Blueprint {
    protected $_table;
    protected $_defaults;
    protected $_associations;

    public function __construct($table, $defaults, $associations = array()) {
        $this->_table = $table;
        $this->_defaults = $defaults;
        $this->_associations = $associations;

        if(!is_array($associations)) {
            throw new Exception("\$associations must be an array of Phactory_Association objects");
        }
    }

    /*
     * Reify a Blueprint as a Phactory_Row. Optionally use an array
     * of associated objects to set fk columns.
     *
     * @param array $associated  [table name] => [Phactory_Row]
     */
    public function create($associated = array()) {
        $assoc_keys = array();
        if($associated) {
            foreach($associated as $name => $row) {
                if(!isset($this->_associations[$name])) {
                    throw new Exception("No association '$name' defined");
                }

                $association = $this->_associations[$name];
                $fk_column = $association->getFromColumn();
                $assoc_keys[$fk_column] = $row->getId();
            }
        }

        return new Phactory_Row($this->_table, array_merge($this->_defaults, $assoc_keys));
    }
}
