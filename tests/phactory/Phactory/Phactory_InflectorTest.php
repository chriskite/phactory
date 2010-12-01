<?php

/**
 * Test class for Phactory_Inflector.
 */
class Phactory_InflectorTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {
        require_once PHACTORY_PATH . '/Inflector.php';
        require_once PHACTORY_PATH . '/Phactory/Inflector.php';
    }

    public function tearDown() {
        Phactory_Inflector::reset();
    }

    public function inflectionProvider() {
        return array(
                array('test', 'tests'),
                array('octopus', 'octopi'),
                array('fish', 'fish'),
                array('user', 'users'));
    }

    public function inflectionExceptionProvider() {
        return array(
                array('fish', 'fishes'),
                array('content', 'content'),
                array('anecdote', 'data'));
    }

    /**
     * @dataProvider inflectionProvider
     */
    public function testPluralize($singular, $plural) {
        $this->assertEquals(Phactory_Inflector::pluralize($singular), $plural);
    }

    /**
     * @dataProvider inflectionExceptionProvider
     */
    public function testAddException($singular, $plural) {
        $this->assertNotEquals(Phactory_Inflector::pluralize($singular), $plural);
        Phactory_Inflector::addException($singular, $plural);
        $this->assertEquals(Phactory_Inflector::pluralize($singular), $plural);
    }
}
