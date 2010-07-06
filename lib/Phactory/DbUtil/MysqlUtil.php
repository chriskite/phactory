<?

class Phactory_DbUtil_MysqlUtil {

	public function __construct() {
		$this->_pdo = Phactory::getConnection();
    }

	public function getPrimaryKey($table) {
		$stmt = $this->_pdo->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
		$result = $stmt->fetch();
		return $result['Column_name'];
	}

    public function disableForeignKeys() {
        $this->_pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    }

    public function enableForeignKeys() {
        $this->_pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    }
}
