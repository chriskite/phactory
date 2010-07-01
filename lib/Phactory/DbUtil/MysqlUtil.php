<?

class Phactory_DbUtil_MysqlUtil {

	public function __construct() {
		$this->_pdo = Phactory::getConnection();
    }

	public function getPrimaryKey($table)
	{
		$stmt = $this->_pdo->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
		$result = $stmt->fetch();
		return $result['Column_name'];
	}
}
