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
abstract class Mad_Model_Association_Proxy extends Mad_Model_Association_Base
{
    /**
     * Ids to be deleted when a save is performed
     * @var array
     */
    protected $_deleteModel = null;

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
        return isset($this->_loaded['getObject']) &&
               $this->_loaded['getObject'] instanceof Mad_Model_Base;
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
            if (!isset($this->_loaded['getObject'])) {
                $this->_loaded['getObject'] = '';
            }
        // set as not loaded
        } else {
            unset($this->_loaded['getObject']);
        }
    }

    /**
     * Check if the associated object has changed
     *
     * @return  boolean
     */
    public function isChanged()
    {
        return $this->_changed;
    }


    /*##########################################################################
    # Abstract Methods
    ##########################################################################*/

    /**
     * return associated object
     * @param   array   $args
     * @return  object
     */
    abstract function getObject($args=array());

    /**
     * Assigns the associate object, extracts the pk, and sets it as the foreign key
     *
     * @param   array   $args
     */
    public function setObject($args=array())
    {
        $associationModel = isset($args[0]) ? $args[0] : null;
        if (!$associationModel instanceof Mad_Model_Base) {
            throw new Mad_Model_Association_Exception('added objects must be a subclass of Mad_Model_Base');
        }
        
        $this->_deleteModel = $this->getObject();
        $this->_loaded['getObject'] = $associationModel;
        $this->_changed = true;
    }

    /**
     * return new object of the associated type which has been instantiated with attribures and
     *  linked to this object through a foreign key and has NOT be saved()
     *
     * @param   array  $attributes
     * @return  object
     */
    public function buildObject($args=array())
    {
        $attributes = isset($args[0]) ? $args[0] : null;

        $this->_deleteModel = $this->getObject();
        $class = $this->getAssocClass();
        $this->_loaded['getObject'] = new $class($attributes);
        $this->_changed = true;

        return $this->_loaded['getObject'];
    }

    /**
     * return new object of the associated type which has been instantiated with attribures and
     *  linked to this object through a foreign key and HAS be saved()
     *
     * @param   array  $attributes
     * @return  object
     */
    public function createObject($args=array())
    {
        $attributes = isset($args[0]) ? $args[0] : array();
        if (!is_array($attributes)) {
            $msg = 'dynamic create{Object} method must be given an array of attributes.';
            throw new Mad_Model_Association_Exception($msg);
        }
        // make sure the we insert objects in correct order
        if ($this instanceof Mad_Model_Association_HasOne && $this->getModel()->isNewRecord()) {
            $msg = 'The base object must be saved before creating associated objects. '.
                   'Try using build{Object} instead.';
            throw new Mad_Model_Association_Exception($msg);
        }

        $this->_deleteModel = $this->getObject();
        $class = $this->getAssocClass();
        $this->_loaded['getObject'] = new $class($attributes);
        $this->_changed = true;
        $this->save();

        return $this->_loaded['getObject'];
    }
}
