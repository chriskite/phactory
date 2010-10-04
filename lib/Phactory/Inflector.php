<?php

class Phactory_Inflector extends Inflector {

	private static $_exceptions = array();

    /*
     * Static class forbids instantiation.
     */
    private function __construct() { }
	
    /*
     * Pluralize a word, obeying any stored exceptions.
     *
     * @param string $word the word to pluralize
     */
	public static function pluralize($word) {
        foreach(self::$_exceptions as $exception) {
			if($exception['singular'] == $word){
				return $exception['plural'];
			}
        }

        return parent::pluralize($word);
	}

    /*
     * Add an exception to the rules for inflection.
     *
     * @param string $singular the singular form of this word
     * @param string $plural the plurbal form of this word
     */
	public static function addException($singular, $plural) {
		self::$_exceptions[] = array('singular' => $singular,
                                     'plural'   => $plural);
	}

    /*
     * Forget all stored exceptions.
     */
    public static function reset() {
        self::$_exceptions = array();
    }
	
}
