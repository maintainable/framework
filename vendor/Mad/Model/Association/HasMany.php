<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage Association
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * An association between model objects
 * 
 * @category   Mad
 * @package    Mad_Model
 * @subpackage Association
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Model_Association_HasMany extends Mad_Model_Association_Collection
{
    /*##########################################################################
    # Construct/Destruct
    ##########################################################################*/

    /**
     * Construct association object
     * 
     * @param   string  $assocName
     * @param   array   $options
     */
    public function __construct($assocName, $options, Mad_Model_Base $model)
    {
        $valid = array('className', 'foreignKey', 'primaryKey', 'associationPrimaryKey',
                       'include', 'select', 'conditions', 'order', 'finderSql',
                       'dependent' => 'nullify');

        $this->_options   = Mad_Support_Base::assertValidKeys($options, $valid);
        $this->_assocName = $assocName;
        $this->_model     = $model;
        $this->_conn      = $model->connection();

        // get inflections
        $toMethod = Mad_Support_Inflector::camelize($this->_assocName, 'lower');
        $toMethod = str_replace('/', '_', $toMethod);
        $singular = Mad_Support_Inflector::singularize($toMethod);
        $toClass  = ucfirst($singular);

        $this->_methods = array(
            $toMethod          => 'getObjects',      // documents
            $toMethod.'='      => 'setObjects',      // documents=
            $singular.'Ids'    => 'getObjectIds',    // documentIds
            $singular.'Ids='   => 'setObjectIds',    // documentIds=
            $singular.'Count'  => 'getObjectCount',  // documentsCount
            'add'.$toClass     => 'addObject',       // addDocument
            'build'.$toClass   => 'buildObject',     // buildDocument
            'create'.$toClass  => 'createObject',    // createDocument
            Mad_Support_Inflector::pluralize('replace'.$toClass) => 'replaceObjects',  // replaceDocuments
            Mad_Support_Inflector::pluralize('delete'.$toClass)  => 'deleteObjects',   // deleteDocuments
            Mad_Support_Inflector::pluralize('clear'.$toClass)   => 'clearObjects',    // clearDocuments
            Mad_Support_Inflector::pluralize('find'.$toClass)    => 'findObjects',     // findDocuments
        );
    }

    /*##########################################################################
    # Instance Methods
    ##########################################################################*/

    /**
     * Save changes to association. This will only save the object's changes if it
     * has been loaded up from the database and was changed
     */
    public function save()
    {
        $baseModel = $this->getModel();
        $fkName    = $this->getFkName();
        $pkName    = $this->getPkName();
        $fkValue   = $baseModel->$pkName;

        $assocModel  = $this->getAssocModel();
        $assocPkName = $this->getAssocPkName();

        // save associations from associated model objects
        if ($this->isLoaded()) {
            $assocModels = $this->getObjects();

            // set fk on associated models and save all.
            foreach ($assocModels as $assocModel) {
                $assocModel->writeAttribute($fkName, $fkValue);
                $assocModel->save();
            }

        // save associations directly from primary keys
        } elseif ($this->areIdsLoaded()) {
            $assocModelIds = $this->getObjectIds();

            // update all associated records
            $assocModel->updateAll(
                "$fkName = :fkValue",
                "$assocPkName IN (".join(', ', $assocModelIds).")",
                array(':fkValue' => $fkValue));
        }

        // check if we need to delete any associations
        if (!empty($this->_deleteIds)) {
            $assocModel->deleteAll("$assocPkName IN (".join(', ', $this->_deleteIds).")");
            $this->_deleteIds = array();
        }
        $this->_changed = false;
    }

    /**
     * Destroy all objects that are dependent on the base object based on their
     * dependency options. This only applies to hasOne/hasMany/HABTM associations.
     */
    public function destroyDependent()
    {
        // no need to destroy dependent if base model is new
        $baseModel = $this->getModel();
        if ($baseModel->isNewRecord()) return;

        $assocModels = $this->getObjects();
        $fkName      = $this->getFkName();
        $pkName      = $this->getPkName();

        // destroy dependent records
        if ($this->_options['dependent'] == 'destroy') {
            foreach ($assocModels as $assocModel) {
                $assocModel->destroy();
            }

        // deleteAll dependent records
        } elseif ($this->_options['dependent'] == 'deleteAll') {
            $this->getAssocModel()->deleteAll("$fkName = :value",
                                        array(':value' => $baseModel->$pkName));

        // (default) nullify dependent records
        } elseif ($this->_options['dependent'] == 'nullify') {
            $this->getAssocModel()->updateAll("$fkName = NULL", "$fkName = :value",
                                        array(':value' => $baseModel->$pkName));

        // invalid dependency
        } else {
            $assoc = $this->getClass().' hasMany '.$this->getAssocClass();
            $msg = 'Invalid setting for $assoc association "dependent" option';
            throw new Mad_Model_Association_Exception($msg);
        }

        // unset loaded associations
        unset($this->_loaded['getObjects']);
        unset($this->_loaded['getObjectsIds']);
    }

    /**
     * Return array of associated object
     *
     * @param   array   $args
     * @return  object
     */
    public function getObjects($args=array())
    {
        if (!isset($this->_loaded['getObjects'])) {
            $fkName  = $this->getFkName();
            $pkValue = $this->getPkValue();

            if (!empty($pkValue)) {
                // build find options
                $conditions = "$fkName = :pkValue ".$this->_constructConditions();
                $order      = $this->_constructOrder();
                $select     = $this->_constructSelect();
                $include    = $this->_constructInclude();

                $options = array('conditions' => $conditions, 'order'   => $order,
                                 'select'     => $select,     'include' => $include);
                $binds = array(':pkValue' => $pkValue);

                // Query for the associated objects
                $collection = $this->getAssocModel()->find('all', $options, $binds);
                $this->_loaded['getObjects'] = $collection;
            } else {
                $this->_loaded['getObjects'] = array();
            }
        }
        // create model collection of objects if it's an array
        if (!$this->_loaded['getObjects'] instanceof Mad_Model_Collection) {
            $coll = new Mad_Model_Collection($this->getAssocModel(), $this->_loaded['getObjects']);
            $this->_loaded['getObjects'] = $coll;
        }
        return $this->_loaded['getObjects'];
    }

    /**
     * Return the number of associated objects
     *
     * @param   array   $args
     * @return  int
     */
    public function getObjectCount($args=array())
    {
        if (!isset($this->_loaded['getObjectCount'])) {
            $fkName  = $this->getFkName();
            $pkValue = $this->getPkValue();

            if (!empty($pkValue)) {
                // additional conditions
                if ($this->_options['conditions']) {
                    $conditions = 'AND '.$this->_options['conditions'];
                } else {
                    $conditions = null;
                }
                $options = array('conditions' => "$fkName = :value $conditions");

                // select
                if (isset($this->_options['select'])) {
                    $options['select'] = $this->_options['select'];
                }

                $binds = array(':value' => $pkValue);

                // Query for the associated objects
                $count = $this->getAssocModel()->count($options, $binds);
                $this->_loaded['getObjectCount'] = $count;
            } else {
                $this->_loaded['getObjectCount'] = 0;
            }
        }
        return $this->_loaded['getObjectCount'];
    }

    /**
     * Find an associated object according to the same runs as Mad_Model_Base
     *
     * @param   array   $args
     * @return  array
     */
    public function findObjects($args=array())
    {
        // method arguments - validate
        $type    = isset($args[0]) ? $args[0] : 'all';
        $options = isset($args[1]) ? $args[1] : array();
        $binds   = isset($args[2]) ? $args[2] : array();

        $valid = array('select', 'conditions', 'include', 'order', 'limit', 
                       'offset', 'page', 'perPage');
        $options = Mad_Support_Base::assertValidKeys($options, $valid);

        // keys/values
        $assoc   = $this->_conn->quoteColumnName($this->getAssocTable());
        $fkName  = $this->_conn->quoteColumnName($this->getFkName());
        $pkValue = $this->getPkValue();

        // build find options
        $conditions = "$assoc.$fkName = :pkValue ".$this->_constructConditions($options['conditions']);
        $order      = $this->_constructOrder($options['order']);
        $select     = $this->_constructSelect($options['select']);
        $include    = $this->_constructInclude($options['include']);

        $options = array('conditions' => $conditions, 'order'   => $order,
                         'select'     => $select,     'include' => $include,
                         'order'      => $options['order'],
                         'limit'      => $options['limit'], 
                         'offset'     => $options['offset'], 
                         'page'       => $options['page'], 
                         'perPage'    => $options['perPage']);
        $binds[':pkValue'] = $pkValue;

        // Query for the associated objects
        if (!empty($options['page'])) {
            return $this->getAssocModel()->paginate($options, $binds);
        } else {
            unset($options['page']);
            unset($options['perPage']);
            return $this->getAssocModel()->find($type, $options, $binds);
        }
    }

    /**
     * Returns a new object of the collection type that has been instantiated with attr and
     *  linked to this object through a foreign key but has not yet been saved. This only works
     *  if an associated object already exists
     *
     * @todo    implement this
     *
     * @param   array   $args
     * @return  object
     */
    public function buildObject($args=array())
    {
        $attributes = isset($args[0]) ? $args[0] : null;

        $class = $this->getAssocClass();
        $assocObject = new $class($attributes);

        // assign pk value of base object to the fk of the associated
        $fk = $this->getFkName();
        $assocObject->$fk = $this->getPkValue();

        $this->_loaded['getObjects'][] = $assocObject;

        $this->_changed = true;
        return $assocObject;
    }

    /**
     * Returns a new object of the collection type that has been instantiated with attr
     *  and linked to this object through a foreign key and that has already been saved.
     *  This only works if an associated object already exists
     *
     * @todo    implement this
     *
     * @param   array   $args
     * @return  object
     */
    public function createObject($args=array())
    {
        $attributes = isset($args[0]) ? $args[0] : array();
        if (!is_array($attributes)) {
            $msg = 'dynamic createObject method must be given an array of attributes.';
            throw new Mad_Model_Association_Exception();
        }
        // make sure the we insert objects in correct order
        if ($this->getModel()->isNewRecord()) {
            $msg = 'The base object must be saved before creating associated '.
                   'objects. Try using build{Object} instead.';
            throw new Mad_Model_Association_Exception($msg);
        }

        $class = $this->getAssocClass();
        $assocObject = new $class($attributes);
        $this->_loaded['getObjects'][] = $assocObject;
        $this->save();

        $this->_changed = true;
        return $assocObject;
    }
}
