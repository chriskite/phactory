<?php

namespace Phactory\Sql\Association;

use Phactory\Sql\Phactory;
use Phactory\Sql\Table;

class ManyToManyTest extends \PHPUnit_Framework_TestCase
{
    protected $pdo;
    protected $phactory;

    protected function setUp()
    {
        $this->pdo = new \PDO("sqlite:test.db");
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->phactory = new Phactory($this->pdo);
        $this->pdo->exec("CREATE TABLE users ( id INTEGER PRIMARY KEY, name TEXT )");
        $this->pdo->exec("CREATE TABLE images ( id INTEGER PRIMARY KEY, filename TEXT)");
        $this->pdo->exec("CREATE TABLE users_images ( user_id INTEGER, image_id INTEGER)");
    }

    protected function tearDown()
    {
        $this->phactory->reset();
        $this->pdo->exec("DROP TABLE users");
        $this->pdo->exec("DROP TABLE images");
        $this->pdo->exec("DROP TABLE users_images");
    }

    public function testGuessFromColumn()
    {
        $assoc = new ManyToMany(new Table('image', true, $this->phactory), new Table('users_images', false, $this->phactory));
        $assoc->setFromTable(new Table('user', true, $this->phactory));
        $this->assertEquals('id', $assoc->getFromColumn());
        $this->assertEquals('user_id', $assoc->getFromJoinColumn());
        $this->assertEquals('image_id', $assoc->getToJoinColumn());
        $this->assertEquals('id', $assoc->getToColumn());
    }
}
