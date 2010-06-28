<?

class Phactory_Row {
    protected $_table;
    protected $_storage = array();

    public function __construct($table, $data) {
        $this->_table = $table;
        foreach($data as $key => $value) {
            $this->_storage[$key] = $value;
        }
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
        return $stmt->execute($params);
    }

    public function __get($key) {
        return $this->_storage[$key];
    }

    public function __set($key, $value) {
        $this->_storage[$key] = $value;
    }
}
