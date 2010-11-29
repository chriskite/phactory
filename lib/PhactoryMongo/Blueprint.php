<?php

class Phactory_Blueprint {
    protected $_collection;
    protected $_defaults;
    protected $_sequence;

    public function __construct($name, $defaults) {
        $this->_collection = new Phactory_Collection($name); 
        $this->_defaults = $defaults;
        $this->_sequence = new Phactory_Sequence();

        // TODO setup embeds correctly
    }

    public function setDefaults($defaults) {
        $this->_defaults = $defaults;
    }

    public function addDefault($column, $value) {
        $this->_defaults[$column] = $value;
    }

    public function removeDefault($column) {
        unset($this->_defaults[$column]);
    }

    /*
     * Create document in the database and return it.
     * TODO obey embeds
     *
     * @return array
     */
    public function create($overrides = array()) {
        $this->_evalSequence($data);

        if($overrides) {
            foreach($overrides as $field => $value) {
                $data->$field = $value;
            }
        }

        return $this->_collection->insert($data);
    }

    /*
     * Empty the collection in the database.
     */
    public function recall() {
        $this->_collection->remove();
    }

    protected function _evalSequence(&$data) {
        $n = $this->_sequence->next();
        foreach($data as &$value) {
            if(false !== strpos($value, '$')) {
                $value = eval('return "'. stripslashes($value) . '";');
            }
        }
    }
}
