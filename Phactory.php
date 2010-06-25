<?

class Phactory {
    protected static $_tables = array();
    protected static $_pdo;

    private function __construct() {}

    public static function connect($pdo) {
        self::$_pdo = $pdo;
    }

    public static function getConnection() {
        return self::$_pdo;
    }

    public static function define($table, $defaults) {
        self::$_tables[$table] = new PhactoryBlueprint($table, $defaults);
    }

    public static function create($table, $overrides = array()) {
        if(! ($blueprint = self::$_tables[$table])) ) {
            throw new Exception("No table defined for '$table'");
        }
            
        $row = $blueprint->create();

        foreach($overrides as $field => $value) {
            $row[$field] = $value;
        }
     
        $row->save();

        return $row;
    }

    public static function get($table, $byColumn) {
        //TODO get PhactoryRow from $table by $byColumn key => value
    }

    public static function teardown() {
        foreach(self::$_tables as $table => $blueprint) {
            self::_truncate($table);
        }
    }

    protected static function _truncate($table) {
        $sql = "TRUNCATE :table";
        $stmt = self::$_pdo->prepare($sql);
        return $tmt->execute(array(':table' => $table));
    }
}
