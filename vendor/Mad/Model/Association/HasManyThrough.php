<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage Association
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * An association between model objects
 * 
 * @category   Mad
 * @package    Mad_Model
 * @subpackage Association
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Model_Association_HasManyThrough extends Mad_Model_Association_Collection
{
    /**
     * Class name for 'through' option
     */
    protected $_throughClass = null;

    /**
     * Model instance for 'through' option
     */
    protected $_throughModel = null;


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
        $valid = array('className', 'foreignKey', 'associationForeignKey', 
                       'primaryKey', 'associationPrimaryKey', 'include', 
                       'select', 'conditions', 'order', 'finderSql',
                       'through', 'dependent' => 'nullify');

        $this->_options   = Mad_Support_Base::assertValidKeys($options, $valid);
        $this->_assocName = $assocName;
        $this->_model     = $model;
        $this->_conn      = $model->connection();

        // throw fatal error if through option is invalid
        $this->_throughClass = Mad_Support_Inflector::classify($this->_options['through']);
        class_exists($this->_throughClass);

        // get inflections
        $toMethod = Mad_Support_Inflector::camelize($this->_assocName, 'lower');
        $toMethod = str_replace('/', '_', $toMethod);
        $singular = Mad_Support_Inflector::singularize($toMethod);
        $toClass  = ucfirst($singular);

        $this->_methods = array(
            $toMethod         => 'getObjects',     // tags
            $singular.'Ids'   => 'getObjectIds',   // tagIds
            $singular.'Count' => 'getObjectCount', // tagsCount
            'add'.$toClass    => 'addObject',      // addTag
            Mad_Support_Inflector::pluralize('delete'.$toClass) => 'deleteObjects', // deleteDocuments
            Mad_Support_Inflector::pluralize('clear'.$toClass)  => 'clearObjects',  // clearDocuments
            Mad_Support_Inflector::pluralize('find'.$toClass)   => 'findObjects',   // findDocuments
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
        $baseModel   = $this->getModel();
        $joinTable   = $this->getJoinTable();
        $fkName      = $this->getFkName();
        $assocFkName = $this->getAssocFkName();
        $pkName      = $this->getPkName();
        $assocPkName = $this->getAssocPkName();
        $fkValue     = $baseModel->$pkName;

        // save associations from associated model objects
        $assocIdValues = array();
        if ($this->isLoaded()) {
            $assocModels = $this->getObjects();

            // save all associated models
            foreach ($assocModels as $assocModel) {
                $assocModel->save();

                // join table record
                if ($assocModel->isAssocChanged()) {
                    $assocIdValues[] = $assocModel->$assocPkName;
                }
            }
        }

        // insert each association record
        foreach ($assocIdValues as $assocValue) {
            $sql = "SELECT COUNT(1) FROM $joinTable WHERE ".
                   "$fkName = ".$this->_conn->quote($fkValue)." AND ".
                   "$assocFkName = ".$this->_conn->quote($assocValue);
            $exists = $this->_conn->selectValue($sql);
            if ($exists) continue;

            $sql = "INSERT IGNORE INTO $joinTable ( ".
                   "  $fkName, $assocFkName ".
                   ") VALUES ( ".
                   "  ".$this->_conn->quote($fkValue).
                   ", ".$this->_conn->quote($assocValue)." ".
                   ")";
            $this->_conn->insert($sql, 'Insert');
        }

        // check if we need to delete any associations
        if (!empty($this->_deleteIds)) {
            $sql = "DELETE FROM $joinTable ".
                   " WHERE $fkName = ".$this->_conn->quote($fkValue)." ".
                   "   AND $assocFkName IN (".join(', ', $this->_deleteIds).")";
            $this->_conn->delete($sql, 'Delete');
            $this->_deleteIds = array();
        }
    }

    /**
     * Destroy all objects that are dependent on the base object based on their
     * dependency options. This only applies to hasOne/hasMany/HABTM associations.
     * 
     * hasMany :through is unique in that we only ever delete the associations
     */
    public function destroyDependent()
    {
        // get join table name & fk name
        $joinTable = $this->getJoinTable();
        $fkName    = $this->getFkName();

        // get pk value from base model
        $baseModel = $this->getModel();
        $pkName    = $this->getPkName();
        $fkValue   = $baseModel->$pkName;

        // destroy dependent records
        if ($this->_options['dependent'] == 'destroy') {
            $joinModel = new $this->_throughClass;
            $joins = $joinModel->find('all', array('conditions' => "$fkName = :val"),  
                                             array(':val'       => $fkValue));
            foreach ($joins as $model) {
                $model->destroy();
            }

        // deleteAll dependent records
        } elseif ($this->_options['dependent'] == 'deleteAll') {
            $sql = "DELETE FROM $joinTable ".
                   " WHERE $fkName = ".$this->_conn->quote($fkValue);
            $this->_conn->delete($sql, 'Delete');

        // (default) nullify dependent records
        } elseif ($this->_options['dependent'] == 'nullify') {
            $sql = "UPDATE $joinTable ".
                   "   SET $fkName = NULL ".
                   " WHERE $fkName = ".$this->_conn->quote($fkValue);
            $this->_conn->update($sql, 'Update');

        // invalid dependency
        } else {
            $assoc = $this->getClass().' hasMany '.$this->getAssocClass();
            $msg = 'Invalid setting for $assoc association "dependent" option';
            throw new Mad_Model_Association_Exception($msg);
        }
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
            $this->_loaded['getObjects'] = $this->getObjectsUsingJoin();
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
            $this->_loaded['getObjectCount'] = $this->getObjectCountUsingJoin();
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

        return $this->findObjectsUsingJoin($type, $options, $binds);
    }
}
