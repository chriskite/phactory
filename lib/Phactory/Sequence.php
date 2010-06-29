<?

/*
 * Simple test:
 * 
 * <?php
 * include_once 'Sequence.php';
 * 
 * Phactory_Sequence::create('foo', "\"foo\$n\"");
 * echo Phactory_Sequence::next('foo') . "\n";
 * echo Phactory_Sequence::next('foo') . "\n";
 * Phactory_Sequence::create('bar', "\"bar\$n\"");
 * echo Phactory_Sequence::next('bar') . "\n";
 * echo Phactory_Sequence::next('foo') . "\n";
 */


class Phactory_Sequence {
	
	protected static $_sequences;
	
	protected $_function;
	protected $_value;
		
	private function __construct($format) {
		$format = stripslashes($format);
		$this->_function = create_function('$n', "return $format;");
		$this->_value = 0;
	}
		
	private function getNextValue() {
		$this->_value += 1;
		$f = $this->_function;
		return $f($this->_value);
	}
	
	public static function create($name, $format) {
		self::$_sequences[$name] = new self($format);
	}
	
	public static function next($seq) {
		if(self::$_sequences[$seq]) {
			return self::$_sequences[$seq]->getNextValue();
		} else {
			throw new Exception("Sequence does not exist.");
		}
	}
	
}