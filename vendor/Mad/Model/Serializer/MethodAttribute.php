<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * The base object from which all DataObjects are extended from
 *
 * @category   Mad
 * @package    Mad_Model
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Model_Serializer_MethodAttribute extends Mad_Model_Serializer_Attribute
{
    protected function _computeType()
    {
        $value = $this->_record->{$this->_name}();

        if (is_bool($value)) {
            return 'boolean';
            
        } elseif (is_float($value)) {
            return 'float';
            
        } elseif (is_int($value)) {
            return 'integer';

        } else {
            return 'string';
        }
    }
    
    protected function _computeValue()
    {
        $value = $this->_record->{$this->_name}();
        return $this->_convert($value);
    }
}
