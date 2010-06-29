<?

class Phactory_MysqlUtil {

	public function __construct() {
		$this->$_pdo = Phactory::getConnection();
    }

	public function getPrimaryKey($table)
	{
		$stmt = $this->$_pdo->prepare("SHOW KEYS FROM :table WHERE Key_name = 'PRIMARY'");
		$stmt->execute(array(":table" => $table));
		$result = $stmt->fetch(PDO::FETCH_OBJ);
		
		return $result->Column_name;
	}
}
