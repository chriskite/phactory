<?php

namespace Phactory\Sql;

class TableTest extends \PHPUnit_Framework_TestCase
{
	protected $pdo;
    protected $phactory;

    protected function setUp()
    {
		$this->pdo = new \PDO("sqlite:test.db");
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("CREATE TABLE `users` ( id INTEGER PRIMARY KEY, name TEXT )");
        $this->pdo->exec("CREATE TABLE `settings` ( id INTEGER, name TEXT )");

        $this->phactory = new Phactory($this->pdo);
    }

    protected function tearDown()
    {        
        $this->pdo->exec("DROP TABLE `settings`");
        $this->pdo->exec("DROP TABLE `users`");
		$this->phactory->reset();
    }

    public function testGetPrimaryKeyWhenPrimaryKeyExists()
    {
        $table = new Table('user', true, $this->phactory);
        $this->assertEquals('id', $table->getPrimaryKey());
    }
    
    public function testGetPrimaryKeyWhenPrimaryKeyRegistered()
    {
        $table = new Table('setting', true, $this->phactory);
        $table->setPrimaryKey('id');
        $this->assertEquals('id', $table->getPrimaryKey());
    }


}
?>
