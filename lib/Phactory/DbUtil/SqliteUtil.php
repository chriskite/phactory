<?
require_once('../Phactory.php');

class Phactory_SqliteUtil{

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
		// @TODO find out how to get primary_key columns from sqlite
		$stmt = $this->$_pdo->prepare("");
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_OBJ);
		
		return $result->Column_name;
	}

}