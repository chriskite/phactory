<?php

namespace Phactory\Mongo;

class Blueprint {
    protected $_collection;
    protected $_defaults;
    protected $_sequence;

    public function __construct($name, $defaults, $associations = array(), Phactory $phactory) {
        $this->_collection = new Collection($name, true, $phactory);
        $this->_defaults = $defaults;
        $this->_sequence = new Sequence();

        if(!is_array($associations)) {
            throw new \Exception("\$associations must be an array of Association objects");
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
     * Build the document as an array, but don't save it to the db.
     *
     * @param array $overrides field => value pairs which override the defaults for this blueprint
     * @param array $associated [name] => [Association] pairs
     * @return array the document
     */
    public function build($overrides = array(), $associated = array()) {
        $data = $this->_defaults;
        if($associated) {
            foreach($associated as $name => $document) {
                if(!isset($this->_associations[$name])) {
                    throw new \Exception("No association '$name' defined");
                }

                $association = $this->_associations[$name];

                if(!$association instanceof Association\EmbedsMany &&
                   !$association instanceof Association\EmbedsOne) {
                    throw new \Exception("Invalid association object for '$name'");
                }

                $overrides[$name] = $document;
            }
        }

        $this->_evalSequence($data);

        if($overrides) {
            foreach($overrides as $field => $value) {
                $data[$field] = $value;
            }
        }

        return $data;
    }

    /*
     * Create document in the database and return it.
     *
     * @param array $overrides field => value pairs which override the defaults for this blueprint
     * @param array $associated [name] => [Association] pairs
     * @return array the created document
     */
    public function create($overrides = array(), $associated = array()) {
        $data = $this->build($overrides, $associated);
        $this->_collection->insert($data,array("w"=>1));
        return $data;
    }

    /*
     * Empty the collection in the database.
     */
    public function recall() {
        $this->_collection->remove();
    }

    protected function _evalSequence(&$data) {
        $n = $this->_sequence->next();
        array_walk_recursive($data,function(&$value) use ($n) {
            if(is_string($value) && false !== strpos($value, '$')) {
                $value = eval('return "'. stripslashes($value) . '";');
            }
        });
    }
}
