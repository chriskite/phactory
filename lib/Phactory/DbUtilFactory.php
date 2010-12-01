<?php

require_once('DbUtil/MysqlUtil.php');
require_once('DbUtil/SqliteUtil.php');

class Phactory_DbUtilFactory {
	
	private function __construct(){ }
	
	public static function getDbUtil()
	{
		$db_type = Phactory::getConnection()->getAttribute(PDO::ATTR_DRIVER_NAME);
		switch ($db_type)
		{
			case 'mysql':
				return new Phactory_DbUtil_MysqlUtil();
				break;
			case 'sqlite':
				return new Phactory_DbUtil_SqliteUtil();
				break;
			default:
				throw new Exception("DB type '$db_type' not found");
				break;
		}
	}

}
