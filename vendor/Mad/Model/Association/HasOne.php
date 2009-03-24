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
class Mad_Model_Association_HasOne extends Mad_Model_Association_Proxy
{
    /*##########################################################################
    # Construct/Destruct
    ##########################################################################*/

    /**
     * Construct association object
     * 
     * @param   string  $assocName
     * @param   array   $options
     * @param   object  $model
     */
    public function __construct($assocName, $options, Mad_Model_Base $model)
    {
        $valid = array('className', 'foreignKey', 'primaryKey', 'include',  
                       'order', 'dependent' => 'nullify');
        $this->_options = Mad_Support_Base::assertValidKeys($options, $valid);
        $this->_assocName = $assocName;
        $this->_model     = $model;
        $this->_conn      = $model->connection();

        // get inflections
        $toMethod = Mad_Support_Inflector::camelize($this->_assocName, 'lower');
        $toMethod = str_replace('/', '_', $toMethod);
        $toClass  = ucfirst($toMethod);

        $this->_methods = array(
            $toMethod         => 'getObject',   // folder
            $toMethod.'='     => 'setObject',   // folder=
            'build'.$toClass  => 'buildObject', // buildFolder
            'create'.$toClass => 'createObject' // createFolder
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
        if ($this->isLoaded()) {
            if ($this->_deleteModel) { $this->destroyDependent(); }

            $baseModel  = $this->getModel();
            $assocModel = $this->getObject();
            $fkName     = $this->getFkName();
            $pkName     = $this->getPkName();

            // set foreign key on associated model before saving
            $assocModel->writeAttribute($fkName, $baseModel->$pkName);
            $assocModel->save();
        }
    }

    /**
     * Destroy all objects that are dependent on the base object based on their
     * dependency options. This only applies to hasOne/hasMany/HABTM associations.
     */
    public function destroyDependent()
    {
        // no need to destroy dependent if base model is new
        if ($this->getModel()->isNewRecord()) return;

        $fkName = $this->getFkName();
        $value  = $this->getPkValue();
        // we assign a model for deletion when assigning a new object
        if (!empty($this->_deleteModel)) {
            $assocModel = $this->_deleteModel;
        } else {
            $assocModel = $this->getObject();
        }
        if (!$assocModel) return;

        // delete associated records
        if ($this->_options['dependent'] == 'destroy') {
            $assocModel->destroy();

        // (default) nullify associated records 
        } elseif ($this->_options['dependent'] == 'nullify') {
            $assocModel->updateAll("$fkName = NULL", "$fkName = :val", 
                                   array(':val' => $value));
        // invalid dependency
        } else {
            $assoc = $this->getClass().' hasOne '.$this->getAssocClass();
            $msg = 'Invalid setting for $assoc association "dependent" option';
            throw new Mad_Model_Association_Exception($msg);
        }
    }

    /**
     * return associated object
     *
     * @param   array   $args
     * @return  object
     */
    public function getObject($args=array())
    {
        if (!isset($this->_loaded['getObject'])) {
            $table   = $this->getAssocTable();
            $pkValue = $this->getPkValue();
            $fkName  = $this->getFkName();
            
            $order = $this->_constructOrder();

            // query for associated object
            $options = array('conditions' => "$table.$fkName = :value", 
                             'order'      => $order);
            if (!empty($this->_options['include'])) {
              $options['include'] = $this->_options['include'];
            }
            $bind = array(':value' => $pkValue);

            // load associated object
            if (!empty($pkValue)) {
                $object = $this->getAssocModel()->find('first', $options, $bind);
                $this->_loaded['getObject'] = $object;
            } else {
                $this->_loaded['getObject'] = null;
            }
        }
        return $this->_loaded['getObject'];
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
}
