<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage Validation
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * Format validation rule for attributes of models before save/insert/update
 *
 * Validate that the data isn't empty
 * Options:
 *  - on:      string  save, create, or update. Defaults to: save
 *  - message: string  Defaults to: "%s can't be empty."
 *
 * @see     Mad_Model_Base::validatesPresenceOf
 * 
 * @category   Mad
 * @package    Mad_Model
 * @subpackage Validation
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Model_Validation_Presence extends Mad_Model_Validation_Base
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
        $valid = array('on' => 'save', 'message' => Mad_Model_Errors::$defaultErrorMessages['empty']);
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
        if (empty($value)) {
            $this->_model->errors->add($column, $this->_options['message']);
        }
    }
}
