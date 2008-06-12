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
class Mad_Model_Serializer_Attribute
{
    protected $_name   = null;
    protected $_record = null;
    protected $_type   = null;
    protected $_value  = null;

    public function __construct($name, $record)
    {
        $this->_name   = $name;
        $this->_record = $record;
        
        $this->_type  = $this->_computeType();
        $this->_value = $this->_computeValue();
    }

    public function getName()
    {
        return $this->_name;
    }

    public function getValue()
    {
        return $this->_value;
    }

    public function getType()
    {
        return $this->_type;
    }

    /**
     * @return  boolean
     */
    public function getNeedsEncoding()
    {
        $types = array('binary', 'date', 'datetime', 'boolean', 'float', 'integer');
        return !in_array($this->_type, $types);
    }

    /** 
     * @param   boolean $includeTypes
     * @return  array
     */
    public function getDecorations($includeTypes = true)
    {
        $decorations = array();
        if ($this->_type == 'binary') {
            $decorations['encoding'] = 'base64';
        }

        if ($includeTypes && $this->_type != 'string') {
            $decorations['type'] = $this->_type;
        }

        if ($this->_value === null) {
            $decorations['null'] = $this->_type;
        }

        return $decorations;
    }


    // Protected

    protected function _computeType()
    {
        $hash = $this->_record->columnsHash();
        $type = $hash[$this->_name]->getType();
     
        if ($type == 'text') {
            return 'string';
        } elseif ($type == 'time') {
            return 'datetime';
        } else {
            return $type;
        }
    }
    
    protected function _computeValue()
    {
        $value = $this->_record->{$this->_name};
        return $this->_convert($value);
    }
    
    protected function _convert($value)
    {
        $conversion = new Mad_Model_Serializer_Conversion;

        if (isset($conversion->xmlFormatting[$this->_type])) {
            $formatter = $conversion->xmlFormatting[$this->_type];
            return $value ? $conversion->{$formatter}($value) : null;
        }
        return $value;
    }
}
