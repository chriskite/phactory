<?

class Phactory_Association_ManyToOne {
    protected $_to_table;
    protected $_from_column;
    protected $_to_column;

    public function __construct($to_table, $from_column, $to_column = null) {
        $this->_to_table = $to_table;
        $this->_from_column = $from_column;
        $this->_to_column = $to_column;

        if(null === $this->_to_column) {
            $db_util = Phactory_DbUtilFactory::getDbUtil();
            $this->_to_column = $db_util->getPrimaryKey($to_table);
            if(!$this->_to_column) {
                throw new Exception("Unable to determine primary key for table '$to_table' and none specified");
            }
        }
    }

    public function getTable() {
        return $this->_to_table;
    }

    public function getFromColumn() {
        return $this->_from_column;
    }

    public function getToColumn() {
        return $this->_to_column;
    }
}
