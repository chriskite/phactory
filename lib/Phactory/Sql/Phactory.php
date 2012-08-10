<?php

namespace Phactory\Sql;

class Phactory {
    /*
     * Array of table name => Blueprint
     */
    protected $_blueprints = array();

    /*
     * PDO database connection
     */
    protected $_pdo;

    /**
     * Constructs a Phactory object for testing SQL databases
     *
     * @param \PDO $pdo A PDO database connection to test with
     */
    public function __construct(\PDO $pdo) {
        $this->_pdo = $pdo;
    }

    /*
     * Set the PDO object to use for database connection.
     *
     * @param object $pdo PDO object
     */
    public function setConnection($pdo) {
        $this->_pdo = $pdo;
    }

    /*
     * Get the PDO database connection object.
     *
     * @return object PDO
     */
    public function getConnection() {
        return $this->_pdo;
    }

    /*
     * Define the default values to use when constructing
     * a row in the specified table.
     *
     * @param string $blueprint_name singular name of the table in the database
     * @param array $defaults key => value pairs of column => value, or a phactory_blueprint
     * @param array $associations array of phactory_associations
     */
    public function define($blueprint_name, $defaults = array(), $associations = array()) {
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
    public function defineBlueprint($blueprint_name, $defaults = array(), $associations = array()) {
        $this->define($blueprint_name, $defaults, $associations);
    }

    /*
     * Instantiate a row in the specified table, optionally
     * overriding some or all of the default values.
     * The row is saved to the database, and returned
     * as a Row.
     *
     * @param string $table name of the table
     * @param array $overrides key => value pairs of column => value
     * @return object Row
     */
    public function create($table, $overrides = array()) {
        return $this->createWithAssociations($table, array(), $overrides);
    }

    /*
     * Build a Row object, optionally
     * overriding some or all of the default values.
     * The row is not saved to the database.
     *
     * @param string $table name of the table
     * @param array $overrides key => value pairs of column => value
     * @return object Row
     */
    public function build($table, $overrides = array()) {
        return $this->buildWithAssociations($table, array(), $overrides);
    }

    /*
     * Instantiate a row in the specified table, optionally
     * overriding some or all of the default values.
     * The row is saved to the database, and returned
     * as a Row.
     *
     * @param string $blueprint_name name of the blueprint to use 
     * @param array $associations [table name] => [Row]
     * @param array $overrides key => value pairs of column => value
     * @return object Row
     */
    public function createWithAssociations($blueprint_name, $associations = array(), $overrides = array()) {
        if(! ($blueprint = $this->_blueprints[$blueprint_name]) ) {
            throw new \Exception("No blueprint defined for '$blueprint_name'");
        }
            
        return $blueprint->create($overrides, $associations);
    }

    /*
     * Build a Row object, optionally
     * overriding some or all of the default values.
     * The row is not saved to the database.
     *
     * @param string $blueprint_name name of the blueprint to use 
     * @param array $associations [table name] => [Row]
     * @param array $overrides key => value pairs of column => value
     * @return object Row
     */
    public function buildWithAssociations($blueprint_name, $associations = array(), $overrides = array()) {
        if(! ($blueprint = $this->_blueprints[$blueprint_name]) ) {
            throw new \Exception("No blueprint defined for '$blueprint_name'");
        }

        foreach($associations as $association) {
            if($association instanceof Association\ManyToMany) {
                throw new \Exception("ManyToMany associations cannot be used in Phactory::build()");
            }
        }
            
        return $blueprint->build($overrides, $associations);
    }

    /*
     * Get a row from the database as a Row.
     * $byColumn is like array('id' => 123).
     *
     * @param string $table_name name of the table 
     * @param array $byColumn
     * @return object Row
     */
    public function get($table_name, $byColumns) {		
        $all = $this->getAll($table_name, $byColumns);
        return array_shift($all);
    }

    public function getAll($table_name, $byColumns) {
        if(!is_array($byColumns)) {
            throw new \Exception("\$byColumns must be an associative array of 'column => value' pairs");
        }

        $table = new Table($table_name, true, $this);
				
        $equals = array();
        $params = array();
		foreach($byColumns as $field => $value)
		{
            $equals[] = $table->quoteIdentifier($field) . ' = ?';
			$params[] = $value;
		}
								
        $where_sql = implode(' AND ', $equals);
        $sql = "SELECT * FROM " . $table->quoteIdentifier($table->getName()) . " WHERE " . $where_sql;

        $stmt = $this->_pdo->prepare($sql);
        $r = $stmt->execute($params);

        if($r === false){
            $error = $stmt->errorInfo();
            Logger::error('SQL statement failed: '.$sql.' ERROR MESSAGE: '.$error[2].' ERROR CODE: '.$error[1]);
        }

        $rows = array();
        while($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $rows[] = new Row($table_name, $result, $this);
        }
        return $rows;
    }

    /*
     * Delete created Row objects from the database.
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

    public function manyToMany($to_table, $join_table, $from_column = null, $from_join_column = null, $to_join_column = null, $to_column = null) {
        $to_table = new Table($to_table, true, $this);
        $join_table = new Table($join_table, false, $this);
        return new Association\ManyToMany($to_table, $join_table, $from_column, $from_join_column, $to_join_column, $to_column);
    }

    /*
     * Create a many-to-one association object for use in define().
     *
     * @param string $to_table the table to associate with
     * @param string $from_column the fk column on the left table
     * @param string $to_column the pk column of the right table, or null to autodetect
     *
     * @return object Association\ManyToOne
     */
    public function manyToOne($to_table, $from_column = null, $to_column = null) {
        $to_table = new Table($to_table, true, $this);
        return new Association\ManyToOne($to_table, $from_column, $to_column);
    }

    /*
     * Create a one-to-one association object for use in define().
     *
     * @param string $to_table the table to associate with
     * @param string $from_column the fk column on the left table
     * @param string $to_column the pk column of the right table, or null to autodetect
     *
     * @return object Association\OneToOne
     */
    public function oneToOne($to_table, $from_column, $to_column = null) {
        $to_table = new Table($to_table, true, $this);
        return new Association\OneToOne($to_table, $from_column, $to_column);
    }

	/*
	 * Specify an exception for table name inflection.
     * For example, if your table of fish is called 'fishes',
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
