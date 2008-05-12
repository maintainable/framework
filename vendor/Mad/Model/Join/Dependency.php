<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage Join
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage Join
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Model_Join_Dependency
{
    /**
     * The array of model objects to build the join. First element
     * is always the base model.
     * @var array
     */
    protected $_joins = array();

    /**
     * The array of association reflections
     * @var array
     */
    protected $_reflections = array();

    /**
     * The 'include' association array used to build the dependency
     * @var array
     */
    protected $_associations = array();

    /**
     * The list of table aliases used to build SQL To make sure we don't duplicate.
     * @var array
     */
    protected $_tableAliases = array();

    /**
     * The hash of base records
     * @var array
     */
    protected $_baseRecordsHash = array();


    /*##########################################################################
    # Construct/Destruct
    ##########################################################################*/

    /**
     * Construct Join dependency
     * @param   object        $model
     * @param   array|string  $associations
     */
    public function __construct(Mad_Model_Base $model, $associations)
    {
        $this->_joins              = array(new Mad_Model_Join_Base($model));
        $this->_associations       = $associations;
        $this->_baseRecordsHash    = array();
        $this->_tableAliases[$model->tableName()] = 1;

        $this->_build($associations);
    }

    /**
     * Stringified version of the object
     * @return  string
     */
    public function __toString()
    {
        foreach ($this->_joins as $join) {
            $joinData[] = $join->__toString();
        }
        return "\n".implode("\n", $joinData);
    }


    /*##########################################################################
    # Public
    ##########################################################################*/

    /**
     * Get all the joins
     *
     * @return  array()
     */
    public function reflections()
    {
        return $this->_reflections;
    }

    /**
     * Get all the joins
     *
     * @return  array()
     */
    public function joins()
    {
        return $this->_joins;
    }

    /**
     * Get the base model join
     *
     * @return  array
     */
    public function joinBase()
    {
        return $this->_joins[0];
    }

    /**
     * Get the association joins
     *
     * @return  array
     */
    public function joinAssociations()
    {
        return array_slice($this->_joins, 1);
    }

    /**
     * Instantiate the model/association given the sql rows
     *
     * @param   array   $rows
     * @return  array
     */
    public function instantiate($rows)
    {
        foreach ($rows as $row) {
            $primaryId = $this->joinBase()->recordId($row);
            if (!isset($this->_baseRecordsHash[$primaryId])) {
                $this->_baseRecordsHash[$primaryId] = $this->joinBase()->instantiate($row);
            }
            $this->_construct($this->_baseRecordsHash[$primaryId], $this->_associations,
                              $this->joinAssociations(), $row);
        }
        return array_values($this->_baseRecordsHash);
    }

    /**
     * Get the list of table aliases
     *
     * @return  array
     */
    public function tableAliases()
    {
        return $this->_tableAliases;
    }

    /**
     * Get the table alias index for the given name
     *
     * @param   string  $name
     * @return  int
     */
    public function tableAlias($name)
    {
        return isset($this->_tableAliases[$name]) ? $this->_tableAliases[$name] : 0;
    }

    /**
     * Add a table alias to the list
     *
     * @param   string  $alias
     */
    public function addTableAlias($alias)
    {
        if (isset($this->_tableAliases[$alias])) {
            $this->_tableAliases[$alias]++;
        } else {
            $this->_tableAliases[$alias] = 1;
        }
    }


    /*##########################################################################
    # Private
    ##########################################################################*/

    /**
     * Iterate through associations and build list of joins needed
     *
     * @param   array|string  $associations
     * @param   object        $parent
     */
    protected function _build($associations, $parent=null)
    {
        // previous join is the parent
        if (!isset($parent)) {
            $tmp = array_slice($this->_joins, -1);
            $parent = array_pop($tmp);
        }

        // Association name
        if (is_string($associations)) {
            if (!$reflection = $parent->reflections($associations)) {
                throw new Mad_Model_Exception("Association named \"$associations\" was not found");
            }
            $this->_reflections[] = $reflection;
            $this->_joins[] = new Mad_Model_Join_Association($reflection, $this, $parent);

        // Reference to another association
        } elseif (is_array($associations)) {
            foreach ($associations as $key => $val) {
                // array
                if (is_int($key)) {
                    $this->_build($val, $parent);

                // hash
                } else {
                    $this->_build($key, $parent);
                    $this->_build($val);
                }
            }
        }
    }

    /**
     * Construct model from the result row
     *
     * @param   object  $parent
     * @param   array   $associations
     * @param   array   $row
     */
    protected function _construct(Mad_Model_Base $parent, $associations, $joins, $row)
    {
        // Association name
        if (is_string($associations)) {
            // get the join we need
            do {
                $join = array_shift($joins);
            } while ($associations != $join->reflection()->getAssocName());

            $this->_constructAssociation($parent, $join, $row);

         // Reference to another association
        } elseif (is_array($associations)) {
            foreach ($associations as $key => $val) {
                // array
                if (is_int($key)) {
                    $this->_construct($parent, $val, $joins, $row);

                // hash
                } else {
                    // get the join we need
                    do {
                        $join = array_shift($joins);
                    } while ($key != $join->reflection()->getAssocName());

                    if ($association = $this->_constructAssociation($parent, $join, $row)) {
                        $this->_construct($association, $val, $joins, $row);
                    }
                }
            }
        }
    }

    /**
     * Construct associations for model from record/row
     *
     * @param   object  $record
     * @param   object  $join
     * @param   array   $row
     */
    protected function _constructAssociation(Mad_Model_Base $record, 
                                             Mad_Model_Join_Base $join, $row)
    {
        // set that we've loaded this association
        $record->setAssociationLoaded($join->reflection()->getAssocName());

        if ($record->id != $join->parent()->recordId($row) ||
            empty($row[$join->aliasedPrimaryKey()])) {
            return;
        }
        $association = $join->instantiate($row);

        $macro = $join->reflection()->getMacro();
        $singular = Mad_Support_Inflector::singularize($join->reflection()->getAssocName());

        if ($macro == 'hasAndBelongsToMany' || $macro == 'hasMany' || 
            $macro == 'hasManyThrough') {
            $addMethod = Mad_Support_Inflector::camelize('add'.ucfirst($singular), 'lower');
            $addMethod = str_replace('/', '_', $addMethod);

            // make sure object isn't already included
            $getter = Mad_Support_Inflector::camelize($join->reflection()->getAssocName(), 'lower');
            $getter = str_replace('/', '_', $getter);
            $exists = array();
            foreach ($record->$getter as $val) { $exists[] = $val->id; }

            if (!in_array($association->id, $exists)) {
                $record->$addMethod($association);
            }

        } elseif ($macro == 'belongsTo' || $macro == 'hasOne') {
            $assignMethod = Mad_Support_Inflector::camelize($singular, 'lower');
            $record->$assignMethod = $association;
        }
        return $association;
    }
}
