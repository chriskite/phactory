<?
require_once('../Phactory.php');

class Phactory_MysqlUtil{

	protected $_pdo;

	public function __construct()
	{ 
		$this->setPdo();
	}
	
	public function setPdo()
	{
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