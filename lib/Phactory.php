<?php

require_once('Phactory/Logger.php');
require_once('Phactory/Sequence.php');
require_once('Phactory/Table.php');
require_once('Phactory/Blueprint.php');
require_once('Phactory/Row.php');
require_once('Phactory/DbUtilFactory.php');
require_once('Phactory/Association/ManyToOne.php');
require_once('Phactory/Association/OneToOne.php');
require_once('Phactory/Association/ManyToMany.php');
require_once('Inflector.php');
require_once('Phactory/Inflector.php');


class Phactory {
    /*
     * Array of table name => Phactory_Blueprint
     */
    protected static $_blueprints = array();

    /*
     * PDO database connection
     */
    protected static $_pdo;

    /*
     * Static class prohibits instantiation.
     */
    private function __construct() {}

    /*
     * Set the PDO object to use for database connection.
     *
     * @param object $pdo PDO object
     */
    public static function setConnection($pdo) {
        self::$_pdo = $pdo;
    }

    /*
     * Get the PDO database connection object.
     *
     * @return object PDO
     */
    public static function getConnection() {
        return self::$_pdo;
    }

    /*
     * Define the default values to use when constructing
     * a row in the specified table.
     *
     * @param string $blueprint_name singular name of the table in the database
     * @param array $defaults key => value pairs of column => value, or a phactory_blueprint
     * @param array $associations array of phactory_associations
     */
    public static function define($blueprint_name, $defaults = array(), $associations = array()) {
        if($defaults instanceof Phactory_Blueprint) {
            $blueprint = $defaults;
        } else {
            $blueprint = new Phactory_Blueprint($blueprint_name, $defaults, $associations);
        }
        self::$_blueprints[$blueprint_name] = $blueprint;
    }

    /*
     * Instantiate a row in the specified table, optionally
     * overriding some or all of the default values.
     * The row is saved to the database, and returned
     * as a Phactory_Row.
     *
     * @param string $table name of the table
     * @param array $overrides key => value pairs of column => value
     * @return object Phactory_Row
     */
    public static function create($table, $overrides = array()) {
        return self::createWithAssociations($table, array(), $overrides);
    }

    /*
     * Build a Phactory_Row object, optionally
     * overriding some or all of the default values.
     * The row is not saved to the database.
     *
     * @param string $table name of the table
     * @param array $overrides key => value pairs of column => value
     * @return object Phactory_Row
     */
    public static function build($table, $overrides = array()) {
        return self::buildWithAssociations($table, array(), $overrides);
    }

    /*
     * Instantiate a row in the specified table, optionally
     * overriding some or all of the default values.
     * The row is saved to the database, and returned
     * as a Phactory_Row.
     *
     * @param string $blueprint_name name of the blueprint to use 
     * @param array $associations [table name] => [Phactory_Row]
     * @param array $overrides key => value pairs of column => value
     * @return object Phactory_Row
     */
    public static function createWithAssociations($blueprint_name, $associations = array(), $overrides = array()) {
        if(! ($blueprint = self::$_blueprints[$blueprint_name]) ) {
            throw new Exception("No blueprint defined for '$blueprint_name'");
        }
            
        return $blueprint->create($overrides, $associations);
    }

    /*
     * Build a Phactory_Row object, optionally
     * overriding some or all of the default values.
     * The row is not saved to the database.
     *
     * @param string $blueprint_name name of the blueprint to use 
     * @param array $associations [table name] => [Phactory_Row]
     * @param array $overrides key => value pairs of column => value
     * @return object Phactory_Row
     */
    public static function buildWithAssociations($blueprint_name, $associations = array(), $overrides = array()) {
        if(! ($blueprint = self::$_blueprints[$blueprint_name]) ) {
            throw new Exception("No blueprint defined for '$blueprint_name'");
        }

        foreach($associations as $association) {
            if($association instanceof Phactory_Association_ManyToMany) {
                throw new Exception("ManyToMany associations cannot be used in Phactory::build()");
            }
        }
            
        return $blueprint->build($overrides, $associations);
    }

    /*
     * Get a row from the database as a Phactory_Row.
     * $byColumn is like array('id' => 123).
     *
     * @param string $table_name name of the table 
     * @param array $byColumn
     * @return object Phactory_Row
     */
    public static function get($table_name, $byColumns) {		
        return array_shift(self::getAll($table_name, $byColumns));
    }

    public static function getAll($table_name, $byColumns) {
        if(!is_array($byColumns)) {
            throw new Exception("\$byColumns must be an associative array of 'column => value' pairs");
        }

        $table = new Phactory_Table($table_name);
				
        $equals = array();
        $params = array();
		foreach($byColumns as $field => $value)
		{
			$equals[] = '`' . $field .'` = ?';
			$params[] = $value;
		}
								
        $where_sql = implode(' AND ', $equals);
        $sql = "SELECT * FROM `" . $table->getName() . "` WHERE " . $where_sql;

        $stmt = self::$_pdo->prepare($sql);
        $r = $stmt->execute($params);

        if($r === false){
            $error = $stmt->errorInfo();
            Phactory_Logger::error('SQL statement failed: '.$sql.' ERROR MESSAGE: '.$error[2].' ERROR CODE: '.$error[1]);
        }

        $rows = array();
        while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = new Phactory_Row($table_name, $result);
        }
        return $rows;
    }

    /*
     * Delete created Phactory_Row objects from the database.
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

    public static function manyToMany($to_table, $join_table, $from_column = null, $from_join_column = null, $to_join_column = null, $to_column = null) {
        $to_table = new Phactory_Table($to_table);
        $join_table = new Phactory_Table($join_table, false);
        return new Phactory_Association_ManyToMany($to_table, $join_table, $from_column, $from_join_column, $to_join_column, $to_column);
    }

    /*
     * Create a many-to-one association object for use in define().
     *
     * @param string $to_table the table to associate with
     * @param string $from_column the fk column on the left table
     * @param string $to_column the pk column of the right table, or null to autodetect
     *
     * @return object Phactory_Association_ManyToOne
     */
    public static function manyToOne($to_table, $from_column = null, $to_column = null) {
        $to_table = new Phactory_Table($to_table);
        return new Phactory_Association_ManyToOne($to_table, $from_column, $to_column);
    }

    /*
     * Create a one-to-one association object for use in define().
     *
     * @param string $to_table the table to associate with
     * @param string $from_column the fk column on the left table
     * @param string $to_column the pk column of the right table, or null to autodetect
     *
     * @return object Phactory_Association_OneToOne
     */
    public static function oneToOne($to_table, $from_column, $to_column = null) {
        $to_table = new Phactory_Table($to_table);
        return new Phactory_Association_OneToOne($to_table, $from_column, $to_column);
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
	public static function setInflection($singular, $plural){
		Phactory_Inflector::addException($singular, $plural);
	}

}
