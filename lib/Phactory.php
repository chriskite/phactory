<?
require_once('Phactory/Blueprint.php');
require_once('Phactory/Row.php');

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
     * @param array $defaults key => value pairs of column => value
     */
    public static function define($table, $defaults) {
        self::$_tables[$table] = new Phactory_Blueprint($table, $defaults);
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
        if(! ($blueprint = self::$_tables[$table]) ) {
            throw new Exception("No table defined for '$table'");
        }
            
        $row = $blueprint->create();

        foreach($overrides as $field => $value) {
            $row->$field = $value;
        }
     
        $row->save();

        return $row;
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

    /*
     * Truncate table in the database.
     *
     * @param string $table name of the table
     */
    protected static function _truncate($table) {
        $sql = "DELETE FROM $table";
        $stmt = self::$_pdo->prepare($sql);
        return $stmt->execute();
    }
}
