<?php

namespace Phactory\Sql;

class Blueprint {
    protected $_table;
    protected $_defaults;
    protected $_associations = array();
    protected $_sequence;
    protected $_phactory;

    public function __construct($name, $defaults, $associations = array(), Phactory $phactory = null) {
        if(!($phactory instanceof Phactory)) {
            throw new \Exception('$phactory must be an instance of Phactory\Sql\Phactory');
        }

        $this->_table = new Table($name, true, $phactory);
        $this->_defaults = $defaults;
        $this->_sequence = new Sequence();
        $this->_phactory = $phactory;

        foreach($associations as $name => $association) {
            $association->setFromTable($this->_table);
            $this->addAssociation($name, $association);
        }

        if(!is_array($associations)) {
            throw new \Exception("\$associations must be an array of Association objects");
        }
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
        $association->setFromTable($this->_table);
        $this->_associations[$name] = $association;
    }

    public function removeAssociation($name) {
        unset($this->_associations[$name]);
    }

    /*
     * Build a Row from this Blueprint. Optionally use an array
     * of associated objects to set fk columns.
     *
     * Note that this function ignores ManyToMany associations, as those
     * can't be handled unless the Row is actually saved to the db.
     *
     * @param array $associated  [table name] => [Row]
     */
    public function build($overrides = array(), $associated = array()) {
        // process one-to-one and many-to-one relations
        $assoc_keys = array();
        foreach($associated as $name => $row) {
            if(!isset($this->_associations[$name])) {
                throw new \Exception("No association '$name' defined");
            }

            $association = $this->_associations[$name];
            if(!$association instanceof Association\ManyToMany) {
                $fk_column = $association->getFromColumn();
                $to_column = $association->getToColumn();
                $assoc_keys[$fk_column] = $row->$to_column;
            }
        }

        $data = array_merge($this->_defaults, $assoc_keys);

        $this->_evalSequence($data);

        $built = new Row($this->_table, $data, $this->_phactory);

        if($overrides) {
            foreach($overrides as $field => $value) {
                $built->$field = $value;
            }
        }

        return $built;
    }

    /*
     * Reify a Blueprint as a Row. Optionally use an array
     * of associated objects to set fk columns.
     *
     * @param array $associated  [table name] => [Row]
     */
    public function create($overrides = array(), $associated = array()) {
        $built = $this->build($overrides, $associated);

        // process any many-to-many associations
        $many_to_many = array();
        foreach($associated as $name => $row) {
            $association = $this->_associations[$name];
            if($association instanceof Association\ManyToMany) {
                if(!is_array($row)) {
                    $row = array($row);
                }
                $many_to_many[$name] = array($row, $association);
            }
        }

        $built->save();

        if($many_to_many) {
            $this->_associateManyToMany($built, $many_to_many);
        }

        return $built;
    }

    /*
     * Truncate table in the database.
     */
    public function recall() {
        $db_util = DbUtilFactory::getDbUtil($this->_phactory);
        $db_util->disableForeignKeys();

        try {
            $sql = "DELETE FROM {$this->_table->getName()}";
            $this->_phactory->getConnection()->exec($sql);
        } catch(Exception $e) { }

        foreach($this->_associations as $association) {
            if($association instanceof Association\ManyToMany) {
                try {
                    $sql = "DELETE FROM `{$association->getJoinTable()}`";
                    $this->_phactory->getConnection()->exec($sql);
                } catch(Exception $e) { }
            }
        }

        $db_util->enableForeignKeys();
    }

    protected function _evalSequence(&$data) {
        $n = $this->_sequence->next();
        foreach($data as &$value) {
            if(false !== strpos($value, '$')) {
                $value = strtr($value, array('$n' => $n));
            }

            if(preg_match_all('/#\{(.+)\}/U', $value, $matches)) {
                foreach($matches[1] as $match) {
                    $value = preg_replace('/#\{.+\}/U', eval('return ' . $match . ';'), $value, 1);
                }
            }
        }
    }

    protected function _associateManyToMany($row, $many_to_many) {
        $pdo = $this->_phactory->getConnection();
        foreach($many_to_many as $name => $arr) {
            list($to_rows, $assoc) = $arr;
            foreach($to_rows as $to_row) {
                $join_table = $assoc->getJoinTable();
                $from_join_column = $assoc->getFromJoinColumn();
                $to_join_column = $assoc->getToJoinColumn();

                // Quote table names as underlying DB expects it
                $sql = "INSERT INTO ".$this->_table->quoteIdentifier($join_table)."
                        (".$this->_table->quoteIdentifier($from_join_column).", ".$this->_table->quoteIdentifier($to_join_column).")
                        VALUES
                        (:from_id, :to_id)";

                $stmt = $pdo->prepare($sql);
                $r = $stmt->execute(array(':from_id' => $row->getId(), ':to_id' => $to_row->getId()));

                if($r === false){
                    $error= $stmt->errorInfo();
                    Logger::error('SQL statement failed: '.$sql.' ERROR MESSAGE: '.$error[2].' ERROR CODE: '.$error[1]);
                }
            }
        }
    }
}
