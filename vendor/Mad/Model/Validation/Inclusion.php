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
 * Validate that the data isn't empty
 * Options:
 *  - on:      string  save, create, or update. Defaults to: save
 *  - message: string  Defaults to: "%s can't be empty."
 *
 * @see     Mad_Model_Base::validatesInclusionOf
 * 
 * @category   Mad
 * @package    Mad_Model
 * @subpackage Validation
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Model_Validation_Inclusion extends Mad_Model_Validation_Base
{
    /**
     * Construct Validation rule
     *
     * @param   array   $attribute
     * @param   array   $options
     */
    public function __construct($attribute, $options)
    {
        $this->_attribute = $attribute;

        $valid = array('in', 
                       'on'        => 'save', 
                       'allowNull' => false,
                       'strict'    => false, 
                       'message'   => Mad_Model_Errors::$defaultErrorMessages['inclusion']);
        $this->_options = Mad_Support_Base::assertValidKeys($options, $valid);

        if (!is_array($this->_options['in']) && !$this->_options['in'] instanceof Traversable) {
            throw new InvalidArgumentException("'in' must be an array or traversable");
        }
    }

    /**
     * Validate attribute
     * @param   string  $column
     * @param   string  $value
     */
    protected function _validate($column, $value)
    {
        // allow null values
        if ($this->_options['allowNull'] && ($value === null)) { return; }

        $found = false;
        if (is_array($this->_options['in'])) {
            // search array
            $found = in_array($value, $this->_options['in'], $this->_options['strict']);
        } else {
            // search traversable object
            foreach ($this->_options['in'] as $v) {
                if ($this->_options['strict']) {
                    $found = ($value === $v);
                } else {
                    $found = ($value == $v);
                }

                if ($found) { break; }
            }
        }
        if (!$found) {
            $this->_model->errors->add($column, $this->_options['message']);
        }
    }
}
