<?

class Phactory_Association_ManyToMany extends Phactory_Association_ManyToOne {
    protected $_join_table;
    protected $_from_join_column;
    protected $_to_join_column;

    public function __construct($to_table, $join_table, $from_column, $from_join_column, $to_join_column, $to_column = null) {
        parent::__construct($to_table, $from_column, $to_column);

        $this->_join_table = $join_table;
        $this->_from_join_column = $from_join_column;
        $this->_to_join_column = $to_join_column;
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
}
