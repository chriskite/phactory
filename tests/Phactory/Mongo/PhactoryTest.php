<?php

namespace Phactory\Mongo;

class PhactoryTest extends \PHPUnit_Framework_TestCase
{
    protected $db;
    protected $phactory;

    protected function setUp()
    {
        $this->mongo = new \Mongo();
        $this->db = $this->mongo->testdb;
        $this->phactory = new Phactory($this->db);
    }

    protected function tearDown()
    {
        $this->phactory->reset();

        $this->db->users->drop();
        $this->db->roles->drop();

        $this->mongo->close();
    }

    public function testSetDb()
    {
        $mongo = new \Mongo();
        $db = $mongo->testdb;
        $this->phactory->setDb($db);
        $db = $this->phactory->getDb();
        $this->assertInstanceOf('MongoDB', $db);
    }

    public function testGetDb()
    {
        $db = $this->phactory->getDb();
        $this->assertInstanceOf('MongoDB', $db);
    }

    public function testDefine()
    {
        // test that define() doesn't throw an exception when called correctly
        $this->phactory->define('user', array('name' => 'testuser'));
    }

    public function testDefineWithClosure()
    {
        // test that define() doesn't throw an exception when called correctly
        $this->phactory->define('user', array('name' => function() {
            return 'testuser';
        }));
    }


    public function testDefineWithBlueprint()
    {
        $blueprint = new Blueprint('user', array('name' => 'testuser'), array(), $this->phactory);
        $this->phactory->define('user', $blueprint);

        $user = $this->phactory->create('user');
        $this->assertEquals('testuser', $user['name']);
    }

    public function testDefineWithAssociations()
    {
        $this->phactory->define('user',
                         array('name' => 'testuser'),
                         array('role' => $this->phactory->embedsOne('role')));
    }

    public function testCreate()
    {
        $name = 'testuser';
        $tags = array('one','two','three');

        // define and create user in db
        $this->phactory->define('user', array('name' => $name, 'tags' => $tags));
        $user = $this->phactory->create('user');

        // test returned array
        $this->assertInternalType('array', $user);
        $this->assertEquals($user['name'], $name);
        $this->assertEquals($user['tags'], $tags);

        // retrieve and test expected document from database
        $db_user = $this->db->users->findOne();
        $this->assertEquals($name, $db_user['name']);
        $this->assertEquals($tags, $db_user['tags']);
    }

    public function testCreateWithClosure()
    {
        $name = 'testuser';
        $tags = array('one','two','three');

        // define and create user in db
        $this->phactory->define('user', array('name' => function() use ($name) {
            return $name;
        }, 'tags' => $tags));
        $user = $this->phactory->create('user');

        // test returned array
        $this->assertInternalType('array', $user);
        $this->assertEquals($user['name'], $name);
        $this->assertEquals($user['tags'], $tags);

        // retrieve and test expected document from database
        $db_user = $this->db->users->findOne();
        $this->assertEquals($name, $db_user['name']);
        $this->assertEquals($tags, $db_user['tags']);
    }

    public function testCreateWithOverrides()
    {
        $name = 'testuser';
        $override_name = 'override_user';

        // define and create user in db
        $this->phactory->define('user', array('name' => $name));
        $user = $this->phactory->create('user', array('name' => $override_name));

        // test returned array
        $this->assertInternalType('array', $user);
        $this->assertEquals($user['name'], $override_name);

        // retrieve and test expected document from database
        $db_user = $this->db->users->findOne();
        $this->assertEquals($db_user['name'], $override_name);
    }

    public function testCreateWithAssociations()
    {
        $this->phactory->define('role',
                         array('name' => 'admin'));
        $this->phactory->define('user',
                         array('name' => 'testuser'),
                         array('role' => $this->phactory->embedsOne('role')));

        $role = $this->phactory->build('role');
        $user = $this->phactory->createWithAssociations('user', array('role' => $role));

        $this->assertEquals($role['name'], $user['role']['name']);
    }

    public function testCreateWithEmbedsManyAssociation() {
        $this->phactory->define('tag',
                         array('name' => 'Test Tag'));
        $this->phactory->define('blog',
                         array('title' => 'Test Title'),
                         array('tags' => $this->phactory->embedsMany('tag')));

        $tag = $this->phactory->build('tag');
        $blog = $this->phactory->createWithAssociations('blog', array('tags' => array($tag)));

        $this->assertEquals('Test Tag', $blog['tags'][0]['name']);

        $this->db->blogs->drop();
    }

    public function testDefineAndCreateWithSequence()
    {
        $tags = array('foo$n','bar$n');
        $this->phactory->define('user', array('name' => 'user\$n', 'tags' => $tags));

        for($i = 0; $i < 5; $i++) {
            $user = $this->phactory->create('user');
            $this->assertEquals("user$i", $user['name']);
            $this->assertEquals(array("foo$i","bar$i"),$user['tags']);
        }
    }

    public function testGet()
    {
        $name = 'testuser';

        // define and create user in db
        $this->phactory->define('user', array('name' => $name));
        $user = $this->phactory->create('user');

        // get() expected row from database
        $db_user = $this->phactory->get('user', array('name' => $name));

        // test retrieved db row
        $this->assertInternalType('array', $db_user);
        $this->assertEquals($name, $db_user['name']);
    }

    public function testRecall()
    {
        $name = 'testuser';

        // define and create user in db
        $this->phactory->define('user', array('name' => $name));
        $user = $this->phactory->create('user');

        // recall() deletes from the db
        $this->phactory->recall();

        // test that the object is gone from the db
        $db_user = $this->db->users->findOne();
        $this->assertNull($db_user);

        // test that the blueprints weren't destroyed too
        $user = $this->phactory->create('user');
        $this->assertEquals($user['name'], $name);
    }
}
?>
