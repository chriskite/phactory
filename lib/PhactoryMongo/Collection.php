<?php

class Phactory_Collection {
    protected $_singular;
    protected $_name;
    protected $_collection;

    public function __construct($singular_name, $pluralize = true) {
        $this->_singular = $singular_name;
        if($pluralize) {
            $this->_name = Phactory_Inflector::pluralize($singular_name);
        } else {
            $this->_name = $singular_name;
        }

        $this->_collection = Phactory::getDb()->selectCollection($this->_name);
    }

    public function getName() {
        return $this->_name;
    }

    public function getSingularName() {
        return $this->_singular;
    }

    public function __toString() {
        return $this->_name;
    }

    public function __call($func, $args) {
        return call_user_func_array(array($this->_collection, $func), $args);
    }
}
