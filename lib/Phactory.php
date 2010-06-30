<?
require_once('Phactory/Sequence.php');
require_once('Phactory/Blueprint.php');
require_once('Phactory/Row.php');
require_once('Phactory/DbUtilFactory.php');
require_once('Phactory/Association/ManyToOne.php');
require_once('Phactory/Association/OneToOne.php');
require_once('Phactory/Association/ManyToMany.php');
require_once('Inflector.php');

class Phactory {
    /*
     * Array of table name => Phactory_Blueprint
     */
    protected static $_tables = array();

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
     * @param string $table name of the table in the database
     * @param array $defaults key => value pairs of column => value, or a Phactory_Blueprint
     * @param array $associations array of Phactory_Associations
     */
    public static function define($table, $defaults, $associations = array()) {
        if($defaults instanceof Phactory_Blueprint) {
            $blueprint = $defaults;
        } else {
            $blueprint = new Phactory_Blueprint($table, $defaults, $associations);
        }
        self::$_tables[$table] = $blueprint;
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
     * Instantiate a row in the specified table, optionally
     * overriding some or all of the default values.
     * The row is saved to the database, and returned
     * as a Phactory_Row.
     *
     * @param string $table name of the table
     * @param array $associations [table name] => [Phactory_Row]
     * @param array $overrides key => value pairs of column => value
     * @return object Phactory_Row
     */
    public static function createWithAssociations($table, $associations = array(), $overrides = array()) {
        if(! ($blueprint = self::$_tables[$table]) ) {
            throw new Exception("No table defined for '$table'");
        }
            
        return $blueprint->create($overrides, $associations);
    }

    /*
     * Get a row from the database as a Phactory_Row.
     * $byColumn is like array('id' => 123).
     *
     * @param string $table name of the table
     * @param array $byColumn
     * @return object Phactory_Row
     */
    public static function get($table, $byColumn) {
        $column = array_keys($byColumn);
        $column = $column[0];
        $value = $byColumn[$column];
        $table = Inflector::pluralize($table);
        
        $sql = "SELECT *
                FROM `$table`
                WHERE `$column` = :value";
        $stmt = self::$_pdo->prepare($sql);
        $stmt->execute(array(':value' => $value));
        $result = $stmt->fetch();
        
        if(false === $result) {
            return null;
        }

        return new Phactory_Row($table, $result);
    }

    /*
     * Delete created Phactory_Row objects from the database.
     */
    public static function recall() {
        foreach(self::$_tables as $table => $blueprint) {
            self::_truncate($table);
        }
    }

    /*
     * Delete created objects from the database,
     * and clear all defined blueprints.
     */
    public static function reset() {
        self::recall();
        self::$_tables = array();
    }

    public static function manyToMany($to_table, $from_column, $from_join_column, $to_join_column, $to_column = null) {
        return new Phactory_Association_ManyToMany($to_table, $from_column, $from_join_column, $to_join_column, $to_column);
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
    public static function manyToOne($to_table, $from_column, $to_column = null) {
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
        return new Phactory_Association_OneToOne($to_table, $from_column, $to_column);
    }

    /*
     * Truncate table in the database.
     *
     * @param string $table name of the table
     */
    protected static function _truncate($table) {
        $table= Inflector::pluralize($table);
    	try {
            $sql = "DELETE FROM $table";
            $stmt = self::$_pdo->prepare($sql);
            return $stmt->execute();
        } catch(Exception $e) { }
    }
}
