<?php

namespace Phactory\Sql;

class DbUtilFactory {
	
	private function __construct(){ }
	
	public static function getDbUtil(Phactory $phactory)
	{
		$db_type = $phactory->getConnection()->getAttribute(\PDO::ATTR_DRIVER_NAME);
		switch ($db_type)
		{
			case 'mysql':
				return new DbUtil\MysqlUtil($phactory);
				break;
			case 'sqlite':
				return new DbUtil\SqliteUtil($phactory);
				break;
			default:
				throw new \Exception("DB type '$db_type' not found");
				break;
		}
	}

}
