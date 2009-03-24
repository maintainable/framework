<?php
/**
 * @category   Mad
 * @package    Mad_Controller
 * @subpackage Validation
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * Format validation rule for attributes of models before save/insert/update
 *
 * Validate the length of the data.
 * Options:
 *  - on:          string save, create, or update. Defaults to: save
 *  - minimum:     int     Value may not be greater than this int
 *  - maximum:     int     Value may not be less than this int
 *  - is:          int     Value must be specific length
 *  - within:      array   The length of value must be in range: eg. array(3, 5)
 *  - allowNull:   bool    Allow null values through
 *
 *  - tooLong:     string Message when 'maximum' is violated
 *                        (default is: "%s is too long (maximum is %d characters)")
 *  - tooShort:    string Message when 'minimum' is violated
 *                        (default is: "%s is too short (minimum is %d characters)")
 *  - wrongLength: string Message when 'is' is invalid.
 *                        (default is: "%s is the wrong length (should be %d characters)")
 *
 * @see     Mad_Model_Base::validatesLengthOf
 *
 * @category   Mad
 * @package    Mad_Controller
 * @subpackage Validation
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Model_Validation_Length extends Mad_Model_Validation_Base
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
        $valid = array('on' => 'save', 'allowNull', 'minimum', 'maximum', 'is', 'within',
                       'tooLong'     => Mad_Model_Errors::$defaultErrorMessages['tooLong'],
                       'tooShort'    => Mad_Model_Errors::$defaultErrorMessages['tooShort'],
                       'wrongLength' => Mad_Model_Errors::$defaultErrorMessages['wrongLength']);
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
        if ($this->_options['allowNull'] && $value === null) { return; }

        // within range
        if ($within = $this->_options['within']) {
            if (!is_array($within) || sizeof($within) != 2) {
                $msg = "'within' argument must be a 2 item array";
                throw new InvalidArgumentException($msg);
            }
            $this->_options['minimum'] = $within[0];
            $this->_options['maximum'] = $within[1];
        }

        // minimum length
        if ($min = $this->_options['minimum']) {
            if (strlen($value) < $min) {
                $msg = sprintf($this->_options['tooShort'], $min);
                $this->_model->errors->add($column, $msg);
            }
        }
        // maximum length
        if ($max = $this->_options['maximum']) {
            if (strlen($value) > $max) {
                $msg = sprintf($this->_options['tooLong'], $max);
                $this->_model->errors->add($column, $msg);
            }
        }
        // specific length 
        if ($is = $this->_options['is']) {
            if (strlen($value) != $is) {
                $msg = sprintf($this->_options['wrongLength'], $is);
                $this->_model->errors->add($column, $msg);
            }
        }
    }
}
