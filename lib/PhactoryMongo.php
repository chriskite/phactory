<?php

require_once('PhactoryMongo/Logger.php');
require_once('PhactoryMongo/Sequence.php');
require_once('PhactoryMongo/Table.php');
require_once('PhactoryMongo/Blueprint.php');
require_once('PhactoryMongo/Row.php');
require_once('PhactoryMongo/DbUtilFactory.php');
require_once('PhactoryMongo/Association/ManyToOne.php');
require_once('PhactoryMongo/Association/OneToOne.php');
require_once('PhactoryMongo/Association/ManyToMany.php');
require_once('Inflector.php');
require_once('PhactoryMongo/Inflector.php');


class Phactory {
    /*
     * Array of collection name => Phactory_Blueprint
     */
    protected static $_blueprints = array();

    /*
     * Mongo database object
     */
    protected static $_db;

    /*
     * Static class prohibits instantiation.
     */
    private function __construct() {}

    /*
     * Set the Mongo object to use for database connection.
     *
     * @param object $db Mongo object
     */
    public static function setDb($db) {
        self::$_db = $db;
    }

    /*
     * Get the Mongo database object.
     *
     * @return object Mongo 
     */
    public static function getDb() {
        return self::$_db;
    }

    /*
     * Define the default values to use when constructing
     * a document in the specified collection.
     *
     * @param string $blueprint_name singular name of the collection in the database
     * @param array $defaults key => value pairs of field => value, or a phactory_blueprint
     * @param array $associations array of phactory_associations
     */
    public static function define($blueprint_name, $defaults, $associations = array()) {
        if($defaults instanceof Phactory_Blueprint) {
            $blueprint = $defaults;
        } else {
            $blueprint = new Phactory_Blueprint($blueprint_name, $defaults, $associations);
        }
        self::$_blueprints[$blueprint_name] = $blueprint;
    }

    /*
     * Instantiate a document in the specified collection, optionally
     * overriding some or all of the default values.
     * The document is saved to the database and returned as an array.
     *
     * @param string $collection name of the collection
     * @param array $overrides key => value pairs of column => value
     * @return array
     */
    public static function create($collection, $overrides = array()) {
        return self::createWithAssociations($collection, array(), $overrides);
    }

    /*
     * Instantiate a document in the specified collection, optionally
     * overriding some or all of the default values.
     * The document is saved to the database, and returned as an array.
     *
     * @param string $blueprint_name name of the blueprint to use 
     * @param array $associations [collection name] => [array]
     * @param array $overrides key => value pairs of field => value
     * @return array
     */
    public static function createWithAssociations($blueprint_name, $associations = array(), $overrides = array()) {
        if(! ($blueprint = self::$_blueprints[$blueprint_name]) ) {
            throw new Exception("No blueprint defined for '$blueprint_name'");
        }
            
        return $blueprint->create($overrides, $associations);
    }

    /*
     * Get a document from the database as an array.
     *
     * @param string $collection_name name of the collection 
     * @param array $query a MongoDB query
     * @return array 
     */
    public static function get($collection_name, $query) {		
        if(!is_array($query)) {
            throw new Exception("\$query must be an associative array of 'field => value' pairs");
        }

        $collection = new Phactory_Collection($collection_name);
				
        return $collection->findOne($query);
    }

    /*
     * Get results from the database as an cursor.
     *
     * @param string $collection_name name of the collection 
     * @param array $query a MongoDB query
     * @return MongoCursor
     */
    public static function getAll($collection_name, $query) {		
        if(!is_array($query)) {
            throw new Exception("\$query must be an associative array of 'field => value' pairs");
        }

        $collection = new Phactory_Collection($collection_name);
				
        return $collection->find($query);
    }

    /*
     * Delete created documents from the database.
     */
    public static function recall() {
        foreach(self::$_blueprints as $blueprint) {
            $blueprint->recall();
        }
    }

    /*
     * Delete created objects from the database, clear defined
     * blueprints, and clear stored inflection exceptions.
     */
    public static function reset() {
        self::recall();
        self::$_blueprints = array();
        Phactory_Inflector::reset();
    }

	/*
	 * Specify an exception for collection name inflection.
     * For example, if your collection of fish is called 'fishes',
     * call setInflection('fish', 'fishes')
     *
	 * @param string $singular singular form of the word.
	 * @param string $plural plural form of the word.
	 *
	 */
	public static function setInflection($singular, $plural){
		Phactory_Inflector::addException($singular, $plural);
	}

}
