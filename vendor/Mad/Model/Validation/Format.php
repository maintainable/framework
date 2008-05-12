<?php
/**
 * @category   Mad
 * @package    Mad_Controller
 * @subpackage Validation
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * Format validation rule for attributes of models before save/insert/update
 * Validate the format of the data using ctype or regex.
 * Options:
 *  - on:      string  save, create, or update. Defaults to: save
 *  - with:    string  The ctype/regex to validate against
 *                     [alpha], [digit], [alnum], or /regex/
 *  - message: string  Custom error message (default is: "is invalid")
 *
 * @see     Mad_Model_Base::validatesFormatOf
 *
 * @category   Mad
 * @package    Mad_Controller
 * @subpackage Validation
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Model_Validation_Format extends Mad_Model_Validation_Base
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
        $this->_attribute = $attribute;

        // verify options
        $valid = array('on' => 'save', 'with', 'message' => Mad_Model_Errors::$defaultErrorMessages['invalid']);
        $this->_options = Mad_Support_Base::assertValidKeys($options, $valid);

        // validate regular expression
        if (@preg_match($this->_options['with'], '') === false) {
            $msg = "Bad regular expression '{$this->_options['with']}'";
            throw new UnexpectedValueException($msg);
        }        
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
        switch (true) {
        case $this->_options['with'] == "[digit]":
            $valid = ctype_digit($value);
            break;
        case $this->_options['with'] == "[alpha]":
            $valid = ctype_alpha($value);
            break;
        case $this->_options['with'] == "[alnum]":
            $valid = ctype_alnum($value);
            break;
        case !empty($this->_options['with']):
            $valid = preg_match($this->_options['with'], $value) ? true : false;
            break;
        default:
            $valid = true;
        }

        if (!$valid) {
            $this->_model->errors->add($column, $this->_options['message']);
        }
    }
}
