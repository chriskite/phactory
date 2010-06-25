<?

class PhactoryRow {
    const PROTECTED_PROPERTIES = array('_table');
    protected $_table;

    public function __construct($table, $data) {
        $this->_table = $table;

        foreach($data as $key => $value) {
            $this->$$key = $value;
        }
    }

    public function save() {
        $pdo = Phactory::getConnection();
        $sql = "INSERT INTO {$this->_table} (";

        $data = array();
        $params = array();
        foreach($this as $key => $value) {
            if(!in_array(self::PROTECTED_PROPERTIES, $key)) {
                $data["`$key`"] = ":$key";
                $params[":$key"] = $value;
            }
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
}
