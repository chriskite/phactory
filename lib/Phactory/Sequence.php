<?php

class Phactory_Sequence {
	
	protected $_value;
		
	public function __construct($initial_value = 0) {
		$this->_value = $initial_value;
	}
		
	public function next() {
        return $this->_value++;
	}
	
}
