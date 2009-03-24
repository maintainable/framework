<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * The base object from which all DataObjects are extended from
 *
 * @category   Mad
 * @package    Mad_Model
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Model_Serializer_PropertyAttribute extends Mad_Model_Serializer_Attribute
{
    protected function _computeType()
    {
        $value = $this->_record->{$this->_name};

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
        $value = $this->_record->{$this->_name};
        return $this->_convert($value);
    }
}
