<?
require_once('../Phactory.php');
require_once('DbUtil/Blueprint.php');
require_once('DbUtil/Row.php');

class Phactory_DbFactory {
	
	protected $_dbType;

	public function __construct(){ }
	
	public function setDbType()
	{
		$pdo = Phactory::getConnection();
		$this->_dbType = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
	}

	public static function getDbUtil()
	{
		$this->setDbType();
	
		switch ($this->_dbType)
		{
			case 'mysql':
				return new Phactory_MysqlUtil();
				break;
			case 'sqlite':
				return new Phactory_SqliteUtil();
				break;
			default:
				throw new Exception('DB type not found');
				break;
		}
	}

}