<?php

namespace Phactory\Sql;

class RowTest extends \PHPUnit_Framework_TestCase
{
	protected $pdo;
    protected $phactory;

    protected function setUp()
    {
		$this->pdo = new \PDO("sqlite:test.db");
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("CREATE TABLE `users` ( id INTEGER PRIMARY KEY, name TEXT )");

        $this->phactory = new Phactory($this->pdo);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {        
		$this->phactory->reset();

        $this->pdo->exec("DROP TABLE `users`");
    }

    public function testGetId()
    {
        $row = new Row('user', array('name' => 'testuser'), $this->phactory);
        $row->save();

        $this->assertEquals($row->getId(), $row->id);
    }

    public function testSave()
    {
		$name = "testuser";
		
		//create Phactory\Sql\Row object and add user to table
		$phactory_row = new Row('user', array('name' => $name), $this->phactory);
		$phactory_row->save();
		
        // retrieve expected user from database
        $stmt = $this->pdo->query("SELECT * FROM `users`");
        $db_user = $stmt->fetch();

		// test retrieved db row
        $this->assertEquals($db_user['name'], $name);
    }

    public function testToArray()
    {
        $data = array('name' => 'testname');
        $row = new Row('user', $data, $this->phactory);
        $arr = $row->toArray();

        $this->assertEquals($data, $arr);

        //changing the returned array shouldn't change the row
        $arr['name'] = 'foo';
        $this->assertNotEquals($row->name, $arr['name']);
    }

    public function testToArrayAfterCreate()
    {
        $data = array('id' => 1, 'name' => 'testname');
        $this->phactory->define('user', $data);
        $user = $this->phactory->create('user');

        $this->assertEquals($data, $user->toArray());
    }

}
?>
