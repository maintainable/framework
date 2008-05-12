<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage Association
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * An association between model objects
 * 
 * @category   Mad
 * @package    Mad_Model
 * @subpackage Association
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
abstract class Mad_Model_Association_Collection extends Mad_Model_Association_Base
{
    /**
     * Ids to be deleted when a save is performed
     * @var array
     */
    protected $_deleteIds = array();

    /**
     * Ids to be replace when a save is performed
     * @var array
     */
    protected $_replaceIds = array();

    /*##########################################################################
    # Implementation of abstract methods
    ##########################################################################*/

    /**
     * An object is considered loaded once the assocation model has been
     * populated from hitting the database, or explicitly flagged as loaded
     *
     * @return  boolean
     */
    public function isLoaded()
    {
        return isset($this->_loaded['getObjects']);
    }

    /**
     * Set that this association's object data as loaded
     *
     * @param   boolean $loaded
     */
    public function setLoaded($loaded=true)
    {
        // set as loaded
        if ($loaded) {
            if (!isset($this->_loaded['getObjects'])) {
                $this->_loaded['getObjects'] = array();
            }
        // set as not loaded
        } else {
            unset($this->_loaded['getObjects']);
        }
    }

    /**
     * An object is considered loaded once the assocation model id's have been
     * populated from hitting the database, or explicitly flagged as loaded
     *
     * @return  boolean
     */
    public function areIdsLoaded()
    {
        return isset($this->_loaded['getObjectIds']);
    }

    /**
     * Set that this association's object ids data as loaded
     *
     * @param   boolean $loaded
     */
    public function setIdsLoaded($loaded=true)
    {
        // set as loaded
        if ($loaded) {
            if (!isset($this->_loaded['getObjectIds'])) {
                $this->_loaded['getObjectIds'] = array();
            }
        // set as not loaded
        } else {
            unset($this->_loaded['getObjectIds']);
        }
    }

    /**
     * Check if any of the associated objects have changed
     *
     * @return  booelan
     */
    public function isChanged()
    {
        return $this->_changed;
    }


    /*##########################################################################
    # Instance Methods
    ##########################################################################*/

    /**
     * Return array of associated object
     *
     * @param   array   $args
     * @return  array
     */
    abstract public function getObjects($args=array());

    /**
     * Return the number of associated objects
     *
     * @param   array   $args
     * @return  object
     */
    abstract public function getObjectCount($args=array());
    
    /**
     *
     */
    public function getObjectsUsingJoin()
    {
        // tables
        $assocTable = $this->getAssocTable();
        $joinTable  = $this->getJoinTable();

        // keys
        $fkName      = $this->getFkName();
        $assocFkName = $this->getAssocFkName();
        $assocPkName = $this->getAssocPkName();

        // value
        $assocCols  = $this->getAssocModel()->getColumnStr($assocTable);
        $pkValue    = $this->getPkValue();

        // empty set if there is no pk value
        if (empty($pkValue)) { return array(); }

        // condition args
        $conditions = null;
        if (!empty($this->_options['conditions'])) {
            $conditions = 'AND ('.$this->_options['conditions'].')';
        }

        // build find options
        $options = array('select'     => $assocCols,
                         'from'       => "$assocTable, $joinTable",
                         'conditions' => "$assocTable.$assocPkName = $joinTable.$assocFkName ".
                                         "AND $joinTable.$fkName = :value $conditions");
        if (!empty($this->_options['order'])) {
            $options['order'] = $this->_options['order'];
        }
        // include
        if (isset($this->_options['include'])) {
            $options['include'] = $this->_options['include'];
        }

        $binds = array(':value' => $pkValue);
        return $this->getAssocModel()->find('all', $options, $binds);
    }

    /**
     * @return  int
     */
    public function getObjectCountUsingJoin()
    {
        // tables
        $assocTable = $this->getAssocTable();
        $joinTable  = $this->getJoinTable();

        // keys
        $fkName      = $this->getFkName();
        $assocFkName = $this->getAssocFkName();
        $assocPkName = $this->getAssocPkName();

        // value
        $assocCols  = $this->getAssocModel()->getColumnStr($assocTable);
        $pkValue    = $this->getPkValue();

        // none if there is no pk value
        if (empty($pkValue)) { return 0; }

        // condition args
        $conditions = null;
        if (!empty($this->_options['conditions'])) {
            $conditions = 'AND ('.$this->_options['conditions'].')';
        }

        // build find options
        $options = array('from'       => "$assocTable, $joinTable",
                         'conditions' => "$assocTable.$assocPkName = $joinTable.$assocFkName ".
                                         "AND $joinTable.$fkName = :value $conditions");
        $binds = array(':value' => $pkValue);
        return $this->getAssocModel()->count($options, $binds);
    }

    /**
     * Get the primary keys for the associated objects.
     *
     * @param   array   $args
     * @return  array
     */
    public function getObjectIds($args=array())
    {
        if (!isset($this->_loaded['getObjectIds'])) {
            $this->_loaded['getObjectIds'] = array();
            foreach ($this->getObjects() as $model) {
                $this->_loaded['getObjectIds'][] = $model->id;
            }
        }
        return $this->_loaded['getObjectIds'];
    }

    /**
     * @return  array
     */
    public function findObjectsUsingJoin($type, $options, $binds) 
    {
        $valid = array('conditions', 'include', 'order', 'limit', 'offset');
        $options = Mad_Support_Base::assertValidKeys($options, $valid);

        // tables/keys/values
        $assocTable  = $this->getAssocTable();
        $joinTable   = $this->getJoinTable();
        $fkName      = $this->getFkName();
        $assocFkName = $this->getAssocFkName();
        $assocPkName = $this->getAssocPkName();
        $assocCols   = $this->getAssocModel()->getColumnStr($assocTable);
        $pkValue     = $this->getPkValue();

        // build find options
        $conditions = $this->_constructConditions($options['conditions']);
        $order      = $this->_constructOrder($options['order']);
        $include    = $this->_constructInclude($options['include']);

        // build find options
        $options = array('select'     => $assocCols,
                         'from'       => "$assocTable, $joinTable",
                         'conditions' => "$assocTable.$assocPkName = $joinTable.$assocFkName ".
                                         "AND $joinTable.$fkName = :pkValue $conditions",
                         'limit'      => $options['limit'],
                         'offset'     => $options['offset']);
        $binds[':pkValue'] = $pkValue;
        return $this->getAssocModel()->find($type, $options, $binds);
    }

    /**
     * Set the array of associated objects
     * @param   array   $collection
     */
    public function setObjects($args=array())
    {
        // first destroy dependent records since we're redefining them
        $this->destroyDependent();
        $this->_loaded['getObjects'] = array();
        $this->addObject($args);
    }

    /**
     * Adds multiple objects to the collection by setting their foreign keys to the
     *  collections primary key
     *
     * @param   array   $args
     * @throws  Mad_Model_Association_Exception
     */
    public function addObject($args=array())
    {
        $models = !empty($args[0]) ? $args[0] : null;
        $models = is_array($models) ? $models : array($models);

        // can't set objects by both object and id
        if (isset($this->_loaded['getObjectIds'])) {
            $msg = 'You cannot add objects to an association by both object reference and id';
            throw new Mad_Model_Association_Exception($msg);
        }

        foreach ($models as $model) {
            if (!$model instanceof Mad_Model_Base) {
                throw new Mad_Model_Association_Exception('Added objects must be a subclass of Mad_Model_Base');
            }
            // flag model assoc as changed, and add to collection
            $model->setIsAssocChanged(true);
            $this->_loaded['getObjects'][] = $model;
        }
        $this->_changed = true;
    }

    /**
     * Replaces the collections content by the objects identified by the primary keys
     *
     * @param   array   $args
     * @return  object
     */
    public function setObjectIds($args=array())
    {
        $ids = !empty($args[0]) ? $args[0] : null;
        $ids = is_array($ids) ? $ids : array($ids);

        // first destroy dependent records since we're redefining them
        $this->destroyDependent();
        $this->_loaded['getObjectIds'] = $ids;
        $this->_changed = true;
    }

    /**
     * Replace the set of objects associated with this model object with the new set.
     *  Detects the differences between the current set of children and the new set,
     *  optimizing the database changes accordingly
     *
     * @param   array   $args
     * @return  array
     */
    public function replaceObjects($args=array())
    {
        $models = isset($args[0]) ? $args[0] : null;
        $models = is_array($models) ? $models : array($models);

        // what type of association ref are we using
        $useObjects   = isset($models[0]) && $models[0] instanceof Mad_Model_Base;
        $useObjectIds = isset($models[0]) && is_numeric($models[0]);

        // build array of ids that we're replacing
        $replaceObjects = array();
        foreach ($models as $model) {
            $pk = $useObjects ? $model->id : $model;
            $replaceObjects[$pk] = $model;
        }

        // add ids to the list of deletes that don't exist in the replacement list
        if ($useObjects) {
            foreach ($this->getObjects() as $assocModel) {
                // object isn't in replacements - remove it
                if (!isset($replaceObjects[$assocModel->id])) {
                    $this->_deleteIds[] = $assocModel->id;
                // flag object as changed
                } else {
                    $model->setIsAssocChanged(true);
                }
            }
            unset($this->_loaded['getObjectIds']);
            $this->_loaded['getObjects'] = $models;

        } elseif ($useObjectIds) {
            foreach ($this->getObjectIds() as $assocModelPk) {
                if (!isset($replaceObjects[$assocModelPk])) {
                    $this->_deleteIds[] = $assocModelPk;
                }
            }
            unset($this->_loaded['getObjects']);
            $this->_loaded['getObjectIds'] = $models;
        }

        $this->_removeDeletedFromLoaded();
        $this->_changed = true;
    }

    /**
     * Removes one or more objects from the collection by setting their foreign keys to NULL.
     *  This will also destroy objects if they're declared as belongsTo and dependent on
     *  this model
     *
     * @param   array   $args
     * @return  object
     */
    public function deleteObjects($args=array())
    {
        $models = isset($args[0]) ? $args[0] : null;
        $models = is_array($models) ? $models : array($models);

        // build array of ids that we're deleting
        foreach ($models as $model) {
            $this->_deleteIds[] = $model instanceof Mad_Model_Base ? $model->id : $model;
        }

        $this->_removeDeletedFromLoaded();
        $this->_changed = true;
    }

    /**
     * Removes every object from the collection. This destroys the associated objects if they
     *  are 'depenedent' => destroy, deletes them directly if they are 'dependent' => 'deleteAll'
     *
     * @param   array   $args
     * @return  object
     */
    public function clearObjects($args=array())
    {
        $this->destroyDependent();
        $this->_loaded['getObjects']   = array();
        $this->_loaded['getObjectIds'] = array();
        $this->_changed = true;
    }


    /*##########################################################################
    # SQL Construction from options
    ##########################################################################*/

    /**
     * @param   string  $conditions
     */
    protected function _constructConditions($conditions=null)
    {
        $conditionStr = null;
        if ($this->_options['conditions']) {
            $conditionStr .= 'AND ('.$this->_options['conditions'].')';
        }
        if ($conditions) {
            $conditionStr .= 'AND ('.$conditions.')';
        }
        return $conditionStr;
    }

    /**
     * @param   string  $order
     */
    protected function _constructOrder($order=null)
    {
        $orderStr =  null;
        if (isset($this->_options['order']) && empty($order)) {
            $orderStr = $this->_options['order'];

        } elseif ($order) {
            $orderStr = $order;
        }
        return $orderStr;
    }

    /**
     * @param   string  $select
     */
    protected function _constructSelect($select=null)
    {
        if (isset($this->_options['select']) && empty($select)) {
            $select = $this->_options['select'];
        }
        return $select;
    }

    /**
     * @param   mixed   $include
     */
    protected function _constructInclude($include=null)
    {
        if (isset($this->_options['include']) && empty($include)) {
            $include = $this->_options['include'];
        }
        return $include;
    }

    /**
     * Unset any currently associated ids/objects that are to be deleted
     * during the save.
     */
    protected function _removeDeletedFromLoaded()
    {
        // unset currently associated
        if ($this->isLoaded()) {
            $getObjects = array();
            foreach ($this->getObjects() as $key=>$object) {
                if (!in_array($object->id, $this->_deleteIds)) {
                    $getObjects[] = $object;
                }
            }
            $this->_loaded['getObjects'] = $getObjects;

        } elseif ($this->areIdsLoaded()) {
            $getObjectIds = array();
            foreach ($this->getObjectIds() as $key=>$pk) {
                if (!in_array($pk, $this->_deleteIds)) {
                    $getObjectIds[] = $pk;
                }
            }
            $this->_loaded['getObjectIds'] = $getObjectIds;
        }
    }
}
