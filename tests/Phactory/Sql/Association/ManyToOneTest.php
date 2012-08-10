<?php

namespace Phactory\Sql\Association;

use Phactory\Sql\Phactory;
use Phactory\Sql\Table;

class ManyToOneTest extends \PHPUnit_Framework_TestCase
{
    protected $pdo;
    protected $phactory;

    protected function setUp()
    {
        $this->pdo = new \PDO("sqlite:test.db");
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->phactory = new Phactory($this->pdo);
        $this->pdo->exec("CREATE TABLE users ( id INTEGER PRIMARY KEY, name TEXT )");
        $this->pdo->exec("CREATE TABLE posts ( id INTEGER PRIMARY KEY, name TEXT, user_id INTEGER )");
    }

    protected function tearDown()
    {
        $this->phactory->reset();
        $this->pdo->exec("DROP TABLE users");
        $this->pdo->exec("DROP TABLE posts");
    }

    public function testGuessFromColumn()
    {
        $assoc = new ManyToOne(new Table('user', true, $this->phactory));
        $assoc->setFromTable(new Table('post', true, $this->phactory));
        $this->assertEquals('user_id', $assoc->getFromColumn());
    }
}
