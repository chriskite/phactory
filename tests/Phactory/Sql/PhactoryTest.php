<?php

namespace Phactory\Sql;

class PhactoryTest extends \PHPUnit_Framework_TestCase
{
    protected $pdo;
    protected $phactory;

    protected function setUp()
    {
        $this->pdo = new \PDO("sqlite:test.db");
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("CREATE TABLE `users` ( id INTEGER PRIMARY KEY, name TEXT, role_id INTEGER )");
        $this->pdo->exec("CREATE TABLE `roles` ( id INTEGER PRIMARY KEY, name TEXT )");
        $this->pdo->exec("CREATE TABLE blogs ( id INTEGER PRIMARY KEY, title TEXT )");
        $this->pdo->exec("CREATE TABLE tags ( id INTEGER PRIMARY KEY, name TEXT )");
        $this->pdo->exec("CREATE TABLE blogs_tags ( blog_id INTEGER, tag_id INTEGER )");

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
        $this->pdo->exec("DROP TABLE `roles`");
        $this->pdo->exec("DROP TABLE blogs");
        $this->pdo->exec("DROP TABLE tags");
        $this->pdo->exec("DROP TABLE blogs_tags");
    }

    public function testSetConnection()
    {
        $pdo = new \PDO("sqlite:test.db");
        $this->phactory->setConnection($pdo);
        $pdo = $this->phactory->getConnection();
        $this->assertInstanceOf('PDO', $pdo);
    }

    public function testGetConnection()
    {
        $pdo = $this->phactory->getConnection();
        $this->assertInstanceOf('PDO', $pdo);
    }

    public function testDefine()
    {
        // test that define() doesn't throw an exception when called correctly
        $this->phactory->define('user', array('name' => 'testuser'));

        // define should only require one argument - the blueprint name
        $this->phactory->define('user');
    }


    public function testDefineWithBlueprint()
    {
        $blueprint = new Blueprint('user', array('name' => 'testuser'), array(), $this->phactory);
        $this->phactory->define('user', $blueprint);

        $user = $this->phactory->create('user');
        $this->assertEquals('testuser', $user->name);
    }

    public function testDefineWithAssociations()
    {
        // define with explicit $to_column
        $this->phactory->define('user',
                         array('name' => 'testuser'),
                         array('role' => $this->phactory->manyToOne('roles', 'role_id', 'id')));

        // definie with implicit $to_column
        $this->phactory->define('user',
                         array('name' => 'testuser'),
                         array('role' => $this->phactory->manyToOne('roles', 'role_id')));
    }

    public function testBuild()
    {
        $name = 'testuser';

        // define and build user
        $this->phactory->define('user', array('name' => $name));
        $user = $this->phactory->build('user');

        // test returned Phactory\Sql\Row
        $this->assertInstanceOf('Phactory\Sql\Row', $user);
        $this->assertEquals($user->name, $name);
    }

    public function testBuildWithOverrides()
    {
        $name = 'testuser';
        $override_name = 'override_user';

        // define and build user
        $this->phactory->define('user', array('name' => $name));
        $user = $this->phactory->build('user', array('name' => $override_name));

        // test returned Phactory\Sql\Row
        $this->assertInstanceOf('Phactory\Sql\Row', $user);
        $this->assertEquals($override_name, $user->name);
    }

    public function testBuildWithAssociations()
    {
        $this->phactory->define('role',
                         array('name' => 'admin'));
        $this->phactory->define('user',
                         array('name' => 'testuser'),
                         array('role' => $this->phactory->manyToOne('role', 'role_id')));

        $role = $this->phactory->create('role'); 
        $user = $this->phactory->buildWithAssociations('user', array('role' => $role));

        $this->assertNotNull($role->id);
        $this->assertEquals($role->id, $user->role_id);
    }

    public function testCreate()
    {
        $name = 'testuser';

        // define and create user in db
        $this->phactory->define('user', array('name' => $name));
        $user = $this->phactory->create('user');

        // test returned Phactory\Sql\Row
        $this->assertInstanceOf('Phactory\Sql\Row', $user);
        $this->assertEquals($user->name, $name);

        // retrieve expected row from database
        $stmt = $this->pdo->query("SELECT * FROM `users`");
        $db_user = $stmt->fetch();

        // test retrieved db row
        $this->assertEquals($db_user['name'], $name);
    }

    public function testCreateWithOverrides()
    {
        $name = 'testuser';
        $override_name = 'override_user';

        // define and create user in db
        $this->phactory->define('user', array('name' => $name));
        $user = $this->phactory->create('user', array('name' => $override_name));

        // test returned Phactory\Sql\Row
        $this->assertInstanceOf('Phactory\Sql\Row', $user);
        $this->assertEquals($user->name, $override_name);

        // retrieve expected row from database
        $stmt = $this->pdo->query("SELECT * FROM `users`");
        $db_user = $stmt->fetch();

        // test retrieved db row
        $this->assertEquals($db_user['name'], $override_name);
    }

    public function testCreateWithAssociations()
    {
        $this->phactory->define('role',
                         array('name' => 'admin'));
        $this->phactory->define('user',
                         array('name' => 'testuser'),
                         array('role' => $this->phactory->manyToOne('role', 'role_id')));

        $role = $this->phactory->create('role'); 
        $user = $this->phactory->createWithAssociations('user', array('role' => $role));

        $this->assertNotNull($role->id);
        $this->assertEquals($role->id, $user->role_id);
    }

    public function testCreateWithAssociationsGuessingFromColumn()
    {
        $this->phactory->define('role',
                         array('name' => 'admin'));
        $this->phactory->define('user',
                         array('name' => 'testuser'),
                         array('role' => $this->phactory->manyToOne('role')));

        $role = $this->phactory->create('role'); 
        $user = $this->phactory->createWithAssociations('user', array('role' => $role));

        $this->assertNotNull($role->id);
        $this->assertEquals($role->id, $user->role_id);
    }

    public function testCreateWithManyToManyAssociation() {
        $this->phactory->define('tag',
                         array('name' => 'Test Tag'));
        $this->phactory->define('blog',
                         array('title' => 'Test Title'),
                         array('tag' => $this->phactory->manyToMany('tags', 'blogs_tags', 'id', 'blog_id', 'tag_id', 'id')));

        $tag = $this->phactory->create('tag');
        $blog = $this->phactory->createWithAssociations('blog', array('tag' => $tag));

        $result = $this->pdo->query("SELECT * FROM blogs_tags");
        $row = $result->fetch();
        $result->closeCursor();

        $this->assertNotEquals(false, $row);
        $this->assertEquals($blog->getId(), $row['blog_id']);
        $this->assertEquals($tag->getId(), $row['tag_id']);
    }

    public function testCreateWithManyToManyAssociations() {
        $this->phactory->define('tag',
                         array('name' => 'Test Tag'));
        $this->phactory->define('blog',
                         array('title' => 'Test Title'),
                         array('tags' => $this->phactory->manyToMany('tags', 'blogs_tags', 'id', 'blog_id', 'tag_id', 'id')));

        $tags = array($this->phactory->create('tag'), $this->phactory->create('tag'));
        $blog = $this->phactory->createWithAssociations('blog', array('tags' => $tags));

        $result = $this->pdo->query("SELECT * FROM blogs_tags");
        foreach($tags as $tag) {
            $row = $result->fetch();
            $this->assertNotEquals(false, $row);
            $this->assertEquals($blog->getId(), $row['blog_id']);
            $this->assertEquals($tag->getId(), $row['tag_id']);
        }
        $result->closeCursor();
    }

    public function testDefineAndCreateWithSequence()
    {
        $this->phactory->define('user', array('name' => 'user$n'));

        for($i = 0; $i < 5; $i++) {
            $user = $this->phactory->create('user');
            $this->assertEquals("user$i", $user->name);
        }
    }

    public function testGet()
    {
        $data = array('id' => 1, 'name' => 'testname', 'role_id' => null);
        $this->phactory->define('user', $data);
        $this->phactory->create('user');
        $user = $this->phactory->get('user', array('id' => 1));

        $this->assertEquals($data, $user->toArray());
        $this->assertInstanceOf('Phactory\Sql\Row', $user);
    }

    public function testGetAll()
    {
        $name = 'testuser';

        // define and create users in db
        $this->phactory->define('user', array('name' => $name));
        $users = array($this->phactory->create('user'), $this->phactory->create('user'));

        // get expected rows from database
        $db_users = $this->phactory->getAll('user', array('name' => $name)); 

        // test retrieved db rows
        $this->assertEquals(2, count($db_users));
        $this->assertEquals($name, $db_users[0]->name);
        $this->assertEquals($name, $db_users[1]->name);
        $this->assertInstanceOf('Phactory\Sql\Row', $db_users[0]);
    }

    public function testGetMultiAttributes()
    {
        $name = 'testuser';
        $role_id = 2;

        // define and create user in db
        $this->phactory->define('user', array('name' => $name, 'role_id' => $role_id));
        $user = $this->phactory->create('user');

        // create 2nd user which shouldn't be returned
        $this->phactory->create('user', array('name' => 'user2', 'role_id' => $role_id));

        // get() expected row from database
        $db_user = $this->phactory->get('user', array('name' => $name, 'role_id' => $role_id)); 

        // test retrieved db row
        $this->assertEquals($name, $db_user->name);
        $this->assertEquals($role_id, $db_user->role_id);
        $this->assertInstanceOf('Phactory\Sql\Row', $db_user);
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
        $stmt = $this->pdo->query("SELECT * FROM `users`");
        $db_user = $stmt->fetch();
        $this->assertFalse($db_user);

        // test that the blueprints weren't destroyed too
        $user = $this->phactory->create('user');
        $this->assertEquals($user->name, $name);
    }
}
?>
