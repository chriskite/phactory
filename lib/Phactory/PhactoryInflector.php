<?

class Phactory_Inflector extends Inflector {

	private static $_singular_plural;

    public function __construct() { }
	
	public static function pluralize($word){
		if(self::$_singular_plural[$word] != null){
			return self::$_singular_plural[$word];
		}else{
			return parent::pluralize($word);
		}
	}

	public static function setSingularPlural($singular_plural){
		self::$_singular_plural = $singular_plural;
	}
	
}
