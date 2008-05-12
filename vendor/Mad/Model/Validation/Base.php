<?php
/**
 * @category   Mad
 * @package    Mad_Controller
 * @subpackage Validation
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * Validation rule for attributes of models before save/insert/update
 *
 * @category   Mad
 * @package    Mad_Controller
 * @subpackage Validation
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
abstract class Mad_Model_Validation_Base
{
    /**
     * The model attributes to validate
     * @var array
     */
    protected $_attribute;

    /**
     * The options for the validation rule
     * @var array
     */
    protected $_options;

    /**
     * The model we are performing the validation on
     * @param object
     */
    protected $_model;

    /*##########################################################################
    # Construct/Destruct
    ##########################################################################*/

    /**
     * Construct validation object
     */
    abstract public function __construct($attributes, $options);

    /**
     * Construct association object - simple factory
     * @param   string  $macro (format|length|numericality|presence|uniqueness)
     * @param   string  $attributes
     * @param   array   $options
     */
    public static function factory($macro, $attribute, $options)
    {
        $className = "Mad_Model_Validation_".Mad_Support_Inflector::camelize($macro);
        return new $className($attribute, $options);
    }


    /*##########################################################################
    # Validation Methods
    ##########################################################################*/
    
    /**
     * Perform validation on the given attribute fields
     *
     * @param   string  $on     save|create|update
     * @param   array   $model
     * @return  array   An array of error messages
     */
    public function validate($on, Mad_Model_Base $model)
    {
        $this->_model = $model;

        $errorMsgs = array();
        foreach ($this->_model->getAttributes() as $name => $value) {
            // Only validate specified attribute on given action
            if ($name != $this->_attribute || $on != $this->_options['on']) {
                continue;
            }
            // concrete class takes care of actual validation
            if ($msg = $this->_validate($name, $value)) { return $msg; }
        }
    }

    /**
     * Get the attribute that this validation rule affects
     */
    public function getAttribute()
    {
        return $this->_attribute;
    }

    /*##########################################################################
    # Abstract Methods
    ##########################################################################*/

    /**
     * Validation implemented by concrete subclass
     * @param   string  $column
     * @param   string  $value
     */
    abstract protected function _validate($column, $value);
}
