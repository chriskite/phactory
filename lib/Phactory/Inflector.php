<?

class Phactory_Inflector extends Inflector {

	private static $_singular_plural;

    public function __construct() { }
	
	public static function pluralize($word){
		foreach(self::$_singular_plural as $sp)
		{
			if($sp['singular'] == $word){
				return $sp['plural'];
			}else{
				return parent::pluralize($word);
			}
		}
	}

	public static function setSingularPlural($singular_plural){
		self::$_singular_plural[] = $singular_plural;
	}
	
}
