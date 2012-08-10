<?php

namespace Phactory\Sql\DbUtil;

use Phactory\Sql\Phactory;

class SqliteUtilTest extends \PHPUnit_Framework_TestCase
{
    protected $pdo;
    protected $phactory;

    protected function setUp()
    {
        $this->pdo = new \PDO('sqlite:test.db');
        $this->phactory = new Phactory($this->pdo);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->pdo->exec("DROP TABLE test_table");
    }

    public function testGetPrimaryKey()
    {
        $this->pdo->exec("CREATE TABLE test_table ( id INTEGER PRIMARY KEY, name TEXT )");

        $db_util = new SqliteUtil($this->phactory);

        $pk = $db_util->getPrimaryKey('test_table');

        $this->assertEquals('id', $pk);
    }

    public function testGetColumns() {
        $this->pdo->exec("CREATE TABLE test_table ( id INTEGER PRIMARY KEY, name TEXT, email TEXT, age INTEGER )");

        $db_util = new SqliteUtil($this->phactory);

        $columns = $db_util->getColumns('test_table');

        $this->assertEquals(array('id', 'name', 'email', 'age'), $columns);
    }

}
?>
