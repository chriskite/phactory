<?php

namespace Phactory\Sql;

class BlueprintTest extends \PHPUnit_Framework_TestCase
{
	protected $pdo;
    protected $phactory;

    protected function setUp()
    {
	    $this->pdo = new \PDO("sqlite:test.db");
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("CREATE TABLE `users` ( name VARCHAR(256) )");

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

    public function testCreate()
    {
		$name = 'testuser';
	
		//create Phactory\Sql\Blueprint object and a new Phactory_Row object
		$phactory_blueprint = new Blueprint('user', array('name' => $name), array(), $this->phactory);
		$phactory_row = $phactory_blueprint->create();
		
		//test $phactory_row is of type Phactory\Sql\Row and that object stored name correctly
		$this->assertInstanceOf('Phactory\Sql\Row', $phactory_row);
		$this->assertEquals($phactory_row->name, $name); 
    }
}
?>
