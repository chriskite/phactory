<?php

namespace Phactory\Mongo;

class Phactory {
    /*
     * Array of collection name => Blueprint
     */
    protected $_blueprints = array();

    /*
     * Mongo database object
     */
    protected $_db;

    /**
     * Constructs a Phactory object for testing MongoDB databases
     *
     * @param \MongoDB $mongo A MongoDB database connection to test with
     */
    public function __construct(\MongoDB $mongo) {
        $this->_db = $mongo;
    }

    /*
     * Set the Mongo object to use for database connection.
     *
     * @param object $db Mongo object
     */
    public function setDb(\MongoDB $db) {
        $this->_db = $db;
    }

    /*
     * Get the Mongo database object.
     *
     * @return object Mongo 
     */
    public function getDb() {
        return $this->_db;
    }

    /*
     * Define the default values to use when constructing
     * a document in the specified collection.
     *
     * @param string $blueprint_name singular name of the collection in the database
     * @param array $defaults key => value pairs of field => value, or a phactory_blueprint
     * @param array $associations array of phactory_associations
     */
    public function define($blueprint_name, $defaults, $associations = array()) {
        if($defaults instanceof Blueprint) {
            $blueprint = $defaults;
        } else {
            $blueprint = new Blueprint($blueprint_name, $defaults, $associations, $this);
        }
        $this->_blueprints[$blueprint_name] = $blueprint;
    }

    /*
    * alias for define per @jblotus pull request
    * eventually we should just rename the original function
    */
    public function defineBlueprint($blueprint_name, $defaults, $associations = array()) {
        $this->define($blueprint_name, $defaults, $associations);
    }

    /*
     * Instantiate a document in the specified collection, optionally
     * overriding some or all of the default values.
     * The document is saved to the database and returned as an array.
     *
     * @param string $blueprint_name name of the blueprint
     * @param array $overrides key => value pairs of column => value
     * @return array
     */
    public function create($blueprint_name, $overrides = array()) {
        return $this->createWithAssociations($blueprint_name, array(), $overrides);
    }

    /*
     * Build a document as an array, optionally
     * overriding some or all of the default values.
     * The document is not saved to the database.
     *
     * @param string $blueprint_name name of the blueprint
     * @param array $overrides key => value pairs of column => value
     * @return array
     */
    public function build($blueprint_name, $overrides = array()) {
        return $this->buildWithAssociations($blueprint_name, array(), $overrides);
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
    public function createWithAssociations($blueprint_name, $associations = array(), $overrides = array()) {
        if(! ($blueprint = $this->_blueprints[$blueprint_name]) ) {
            throw new \Exception("No blueprint defined for '$blueprint_name'");
        }
            
        return $blueprint->create($overrides, $associations);
    }

    /*
     * Build a document as an array, optionally
     * overriding some or all of the default values.
     * The document is not saved to the database.
     *
     * @param string $blueprint_name name of the blueprint to use 
     * @param array $associations [collection name] => [array]
     * @param array $overrides key => value pairs of field => value
     * @return array
     */
    public function buildWithAssociations($blueprint_name, $associations = array(), $overrides = array()) {
        if(! ($blueprint = $this->_blueprints[$blueprint_name]) ) {
            throw new \Exception("No blueprint defined for '$blueprint_name'");
        }
            
        return $blueprint->build($overrides, $associations);
    }

    /*
     * Get a document from the database as an array.
     *
     * @param string $collection_name name of the collection 
     * @param array $query a MongoDB query
     * @return array 
     */
    public function get($collection_name, $query) {		
        if(!is_array($query)) {
            throw new \Exception("\$query must be an associative array of 'field => value' pairs");
        }

        $collection = new Collection($collection_name, true, $this);
				
        return $collection->findOne($query);
    }

    /*
     * Get results from the database as a cursor.
     *
     * @param string $collection_name name of the collection 
     * @param array $query a MongoDB query
     * @return MongoCursor
     */
    public function getAll($collection_name, $query = array()) {		
        if(!is_array($query)) {
            throw new \Exception("\$query must be an associative array of 'field => value' pairs");
        }

        $collection = new Collection($collection_name, true, $this);
				
        return $collection->find($query);
    }

    /*
     * Create an embeds-one association object for use in define().
     *
     * @param string $collection_name the singular name of the collection to associate with
     */
    public function embedsOne($collection_name) {
        return new Association\EmbedsOne($collection_name);
    }

    /*
     * Create an embeds-many association object for use in define().
     *
     * @param string $collection_name the singular name of the collection to associate with
     */
    public function embedsMany($collection_name) {
        return new Association\EmbedsMany($collection_name);
    }

    /*
     * Delete created documents from the database.
     */
    public function recall() {
        foreach($this->_blueprints as $blueprint) {
            $blueprint->recall();
        }
    }

    /*
     * Delete created objects from the database, clear defined
     * blueprints, and clear stored inflection exceptions.
     */
    public function reset() {
        $this->recall();
        $this->_blueprints = array();
        Inflector::reset();
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
	public function setInflection($singular, $plural){
		Inflector::addException($singular, $plural);
	}

}
