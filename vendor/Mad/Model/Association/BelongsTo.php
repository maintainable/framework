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
class Mad_Model_Association_BelongsTo extends Mad_Model_Association_Proxy
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
        $valid = array('className', 'foreignKey', 'primaryKey', 'include');

        $this->_options   = Mad_Support_Base::assertValidKeys($options, $valid);
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
            $baseModel   = $this->getModel();
            $assocModel  = $this->getObject();
            $fkName      = $this->getFkName();
            $pkName      = $this->getPkName();

            // save associated object
            $assocModel->save();
            $baseModel->writeAttribute($fkName, $assocModel->$pkName);
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
            $pkName  = $this->getPkName();
            $pkValue = $this->getPkValue();
            $pkValue = !empty($pkValue) ? $pkValue : "0";

            // find Options
            $options = array('conditions' => "$table.$pkName = :value");
            if (!empty($this->_options['include'])) {
              $options['include'] = $this->_options['include'];
            }
            $binds = array(':value' => $pkValue);

            // load associated object
            $object = $this->getAssocModel()->find('first', $options, $binds);
            $this->_loaded['getObject'] = $object;
        }
        return $this->_loaded['getObject'];
    }
}
