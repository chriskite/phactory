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
    public function create($overrides = array(), $associated = array()) {
        $assoc_keys = array();
        $many_to_many = array();
        if($associated) {
            foreach($associated as $name => $row) {
                if(!isset($this->_associations[$name])) {
                    throw new Exception("No association '$name' defined");
                }

                $association = $this->_associations[$name];

                if($association instanceof Phactory_Association_ManyToMany) {
                    $many_to_many[$name] = array($row, $association);
                } else {
                    $fk_column = $association->getFromColumn();
                    $assoc_keys[$fk_column] = $row->getId();
                }
            }
        }

        $row = new Phactory_Row($this->_table, array_merge($this->_defaults, $assoc_keys));

        if($overrides) {
            foreach($overrides as $field => $value) {
                $row->$field = $value;
            }
        }
     
        $row->save();
        
        if($many_to_many) {
            $this->_associateManyToMany($row, $many_to_many);
        }

        return $row;
    }

    protected function _associateManyToMany($row, $many_to_many) {
        $pdo = Phactory::getConnection();
        foreach($many_to_many as $name => $arr) {
            list($to_row, $assoc) = $arr;
            $join_table = $assoc->getJoinTable();
            $from_join_column = $assoc->getFromJoinColumn();
            $to_join_column = $assoc->getToJoinColumn();

            $sql = "INSERT INTO `$join_table` 
                    (`$from_join_column`, `$to_join_column`)
                    VALUES
                    (:from_id, :to_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(':from_id' => $row->getId(), ':to_id' => $to_row->getId()));
        }
    } 
}
