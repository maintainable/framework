<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage Validation
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * Format validation rule for attributes of models before save/insert/update
 *
 * Validate that the data is numeric. (Yes I'm aware numericality is not a real word)
 * Options:
 *  - on:           string  save, create, or update. Defaults to: save
 *  - only_integer: bool    Don't allow floats
 *  - message:      string  Defaults to: "%s is not a number."
 *
 * @see     Mad_Model_Base::validatesNumericalityOf
 * 
 * @category   Mad
 * @package    Mad_Model
 * @subpackage Validation
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Model_Validation_Numericality extends Mad_Model_Validation_Base
{
    /*##########################################################################
    # Construct/Destruct
    ##########################################################################*/

    /**
     * Construct Validation rule
     *
     * @param   array   $attribute
     * @param   array   $options
     */
    public function __construct($attribute, $options)
    {
        // verify options
        $valid = array('on' => 'save', 'allowNull' => false, 'onlyInteger' => true,
                       'message' => Mad_Model_Errors::$defaultErrorMessages['notNumber']);
        $this->_options = Mad_Support_Base::assertValidKeys($options, $valid);
        $this->_attribute = $attribute;
    }


    /*##########################################################################
    # Validation
    ##########################################################################*/

    /**
     * Validate attribute on save
     * @param   string  $column
     * @param   string  $value
     */
    protected function _validate($column, $value)
    {
        // Allow null values through
        if ($this->_options['allowNull'] && ($value === null || $value === '')) {
            return null;
        }

        // Only integers (no floats)
        if ($this->_options['onlyInteger'] && (!is_numeric($value) || strstr($value, '.'))) {
            $this->_model->errors->add($column, $this->_options['message']);

        // validate is any number
        } elseif (!is_numeric($value)) {
            $this->_model->errors->add($column, $this->_options['message']);
        }
    }
}
