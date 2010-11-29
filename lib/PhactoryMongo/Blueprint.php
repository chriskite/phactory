<?php

class Phactory_Blueprint {
    protected $_collection;
    protected $_defaults;
    protected $_sequence;

    public function __construct($name, $defaults, $associations = array()) {
        $this->_collection = new Phactory_Collection($name); 
        $this->_defaults = $defaults;
        $this->_sequence = new Phactory_Sequence();

        if(!is_array($associations)) {
            throw new Exception("\$associations must be an array of Phactory_Association objects");
        }
        $this->setAssociations($associations);
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

    public function setAssociations($associations) {
        $this->_associations = $associations;
    }

    public function addAssociation($name, $association) {
        $this->_associations[$name] = $association;
    }

    public function removeAssociation($name) {
        unset($this->_associations[$name]);
    }

    /*
     * Create document in the database and return it.
     *
     * @return array the created document
     */
    public function create($overrides = array(), $associated = array()) {
        if($associated) {
            foreach($associated as $name => $document) {
                if(!isset($this->_associations[$name])) {
                    throw new Exception("No association '$name' defined");
                }

                $association = $this->_associations[$name];

                if($association instanceof Phactory_Association_EmbedsMany) {
                    // $document can be a single doc or an array of docs
                    if(!is_array($document)) {
                        $document = array($document);
                    }
                } elseif($association instanceof Phactory_Association_EmbedsOne) {
                    // nothing special needed
                } else {
                    throw new Exception("Invalid association object for '$name'");
                }

                $overrides[$name] = $document;
            }
        }

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
