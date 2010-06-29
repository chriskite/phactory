<?

class Phactory_SqliteUtil{

	protected $_pdo;

	public function __construct()
	{ 
		$this->$_pdo = Phactory::getConnection();
	}
	
	public function getPrimaryKey($table)
	{
		$stmt = $this->$_pdo->prepare("SELECT `sql` FROM sqlite_master");
		$stmt->execute();
		$result = $stmt->fetch();
        $sql = $result['sql'];	

        $matches = array();
        preg_match($sql, '/(.*)\s+.*\s+PRIMARY KEY/', $matches);

        if(!$matches[1]) {
            return null;
        }
		return $matches[1]; 
	}

}
