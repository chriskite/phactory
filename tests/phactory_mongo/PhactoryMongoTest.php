<?php

class PhactoryMongoTest extends PHPUnit_Framework_TestCase
{
    protected $db;

    public static function setUpBeforeClass() {
        require_once PHACTORY_PATH . '/PhactoryMongo.php';
    }

    protected function setUp()
    {
        $this->mongo = new Mongo();
        $this->db = $this->mongo->testdb;

        Phactory::setDb($this->db);
    }

    protected function tearDown()
    {
        Phactory::reset();

        $this->db->users->drop();
        $this->db->roles->drop();

        $this->mongo->close();
    }

    public function testSetDb()
    {
        $mongo = new Mongo();
        $db = $mongo->testdb;
        Phactory::setDb($db);
        $db = Phactory::getDb();
        $this->assertInstanceOf('MongoDB', $db);
    }

    public function testGetDb()
    {
        $db = Phactory::getDb();
        $this->assertInstanceOf('MongoDB', $db);
    }

    public function testDefine()
    {
        // test that define() doesn't throw an exception when called correctly
        Phactory::define('user', array('name' => 'testuser'));
    }


    public function testDefineWithBlueprint()
    {
        $blueprint = new Phactory_Blueprint('user', array('name' => 'testuser'));
        Phactory::define('user', $blueprint);

        $user = Phactory::create('user');
        $this->assertEquals('testuser', $user['name']);
    }

    public function testDefineWithAssociations()
    {
        Phactory::define('user',
                         array('name' => 'testuser'),
                         array('role' => Phactory::embedsOne('role')));
    }

    public function testCreate()
    {
        $name = 'testuser';

        // define and create user in db
        Phactory::define('user', array('name' => $name));
        $user = Phactory::create('user');

        // test returned array
        $this->assertInternalType('array', $user);
        $this->assertEquals($user['name'], $name);

        // retrieve and test expected document from database
        $db_user = $this->db->users->findOne();
        $this->assertEquals($name, $db_user['name']);
    }

    public function testCreateWithOverrides()
    {
        $name = 'testuser';
        $override_name = 'override_user';

        // define and create user in db
        Phactory::define('user', array('name' => $name));
        $user = Phactory::create('user', array('name' => $override_name));

        // test returned array 
        $this->assertInternalType('array', $user);
        $this->assertEquals($user['name'], $override_name);

        // retrieve and test expected document from database
        $db_user = $this->db->users->findOne();
        $this->assertEquals($db_user['name'], $override_name);
    }

    public function testCreateWithAssociations()
    {
        Phactory::define('role',
                         array('name' => 'admin'));
        Phactory::define('user',
                         array('name' => 'testuser'),
                         array('role' => Phactory::embedsOne('role')));

        $role = Phactory::build('role'); 
        $user = Phactory::createWithAssociations('user', array('role' => $role));

        $this->assertEquals($role['name'], $user['role']['name']);
    }

    public function testCreateWithEmbedsManyAssociation() {
        Phactory::define('tag',
                         array('name' => 'Test Tag'));
        Phactory::define('blog',
                         array('title' => 'Test Title'),
                         array('tags' => Phactory::embedsMany('tag')));

        $tag = Phactory::build('tag');
        $blog = Phactory::createWithAssociations('blog', array('tags' => array($tag)));

        $this->assertEquals('Test Tag', $blog['tags'][0]['name']);

        $this->db->blogs->drop();
    }

    public function testDefineAndCreateWithSequence()
    {
        Phactory::define('user', array('name' => 'user\$n'));

        for($i = 0; $i < 5; $i++) {
            $user = Phactory::create('user');
            $this->assertEquals("user$i", $user['name']);
        }
    }

    public function testGet()
    {
        $name = 'testuser';

        // define and create user in db
        Phactory::define('user', array('name' => $name));
        $user = Phactory::create('user');

        // get() expected row from database
        $db_user = Phactory::get('user', array('name' => $name)); 

        // test retrieved db row
        $this->assertInternalType('array', $db_user);
        $this->assertEquals($name, $db_user['name']);
    }

    public function testRecall()
    {
        $name = 'testuser';

        // define and create user in db
        Phactory::define('user', array('name' => $name));
        $user = Phactory::create('user');

        // recall() deletes from the db
        Phactory::recall();

        // test that the object is gone from the db
        $db_user = $this->db->users->findOne();
        $this->assertNull($db_user);

        // test that the blueprints weren't destroyed too
        $user = Phactory::create('user');
        $this->assertEquals($user['name'], $name);
    }
}
?>
