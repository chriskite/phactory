<?php

namespace Phactory\Sql;

class RowTest extends \PHPUnit_Framework_TestCase
{
	protected $pdo;
    protected $phactory;
    private $dsn = 'sqlite:test.db';

    protected function setUp()
    {
		$this->pdo = new \PDO($this->dsn);
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
        $this->pdo->exec("DROP TABLE IF EXISTS `busers`");
    }

    public function testGetId()
    {
        $row = new Row('user', array('name' => 'testuser'), $this->phactory);
        $row->save();

        $this->assertEquals($row->getId(), $row->id);
    }

    public function testSave_default_tableNameQuotedWithBackticks()
    {
        $expectedTableName = 'INSERT INTO `busers`';
        $pdo = $this->mockPdo($expectedTableName);
        $phactory = new Phactory($pdo);

        $phactory_row = new Row('buser', array('name' => 'test'), $phactory);
        $phactory_row->save();
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

    public function testFill()
    {
        $data = array('id' => 1);
        $row = new Row('user', $data, $this->phactory);
        $arr = $row->toArray();
        
        $this->assertEquals($data, $arr);

        $data['name'] = null;
        $arr = $row->fill()->toArray();

        $this->assertEquals($data, $arr);
    }

    private function mockPdo($expectedTableName)
    {
        $stmt = $this->getMock('\PDOStatement', array('execute'));
        $stmt->expects($this->any())->method('execute')->will($this->returnValue(true));

        $pdo = $this->getMock('\PDO', array('prepare', 'lastInsertId'), array($this->dsn));
        $pdo->expects($this->any())->method('prepare')->with($this->anything())->will($this->returnValue($stmt));
        $pdo->expects($this->at(0))->method('prepare')->with($this->stringStartsWith($expectedTableName))->will(
            $this->returnValue($stmt)
        );
        $pdo->expects($this->once())->method('lastInsertId')->will($this->returnValue(1));
        return $pdo;
    }
}
?>
