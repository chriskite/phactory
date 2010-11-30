<?php

/**
 * Test class for Phactory_Blueprint.
 * Generated by PHPUnit on 2010-06-28 at 09:18:09.
 */
class Phactory_BlueprintTest extends PHPUnit_Framework_TestCase
{
	protected $pdo;

    protected function setUp()
    {
        require_once PHACTORY_PATH . '/Phactory.php';
        require_once PHACTORY_PATH . '/Phactory/Blueprint.php';
	    $this->pdo = new PDO("sqlite:test.db");
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("CREATE TABLE `users` ( name VARCHAR(256) )");

        Phactory::setConnection($this->pdo);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        Phactory::reset();

        $this->pdo->exec("DROP TABLE `users`");
    }

    public function testCreate()
    {
		$name = 'testuser';
	
		//create Phactory_Blueprint object and a new Phactory_Row object
		$phactory_blueprint = new Phactory_Blueprint('user', array('name' => $name));
		$phactory_row = $phactory_blueprint->create();
		
		//test $phactory_row is of type Phactory_Row and that object stored name correctly
		$this->assertType('Phactory_Row', $phactory_row);
		$this->assertEquals($phactory_row->name, $name); 
    }
}
?>
