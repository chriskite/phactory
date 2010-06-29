<?

class Phactory_Row {
    protected $_table;
    protected $_storage = array();
    protected $_id;

    public function __construct($table, $data) {
        $this->_table = $table;
        foreach($data as $key => $value) {
            $this->_storage[$key] = $value;
        }
    }

    public function getId() {
        return $this->_id;
    }

    public function save() {
        $pdo = Phactory::getConnection();
        $sql = "INSERT INTO {$this->_table} (";

        $data = array();
        $params = array();
        foreach($this->_storage as $key => $value) {
            $data["`$key`"] = ":$key";
            $params[":$key"] = $value;
        }

        $keys = array_keys($data);
        $values = array_values($data);

        $sql .= join(',', $keys);
        $sql .= ") VALUES (";
        $sql .= join(',', $values);
        $sql .= ")";

        $stmt = $pdo->prepare($sql);
        $r = $stmt->execute($params);

        $this->_id = $pdo->lastInsertId();

        $db_util = Phactory_DbUtilFactory::getDbUtil();
        $pk = $db_util->getPrimaryKey($this->_table);
        if($pk) { 
            $this->_storage[$pk] = $this->_id;
        }

        return $r;
    }

    public function __get($key) {
        return $this->_storage[$key];
    }

    public function __set($key, $value) {
        $this->_storage[$key] = $value;
    }
}
