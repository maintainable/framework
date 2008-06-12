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

    /**
     * There is a significant speed improvement if the value
     * does not need to be escaped, as <tt>tag!</tt> escapes all values
     * to ensure that valid XML is generated. For known binary
     * values, it is at least an order of magnitude faster to
     * Base64 encode binary values and directly put them in the
     * output XML than to pass the original value or the Base64
     * encoded value to the <tt>tag!</tt> method. It definitely makes
     * no sense to Base64 encode the value and then give it to
     * <tt>tag!</tt>, since that just adds additional overhead.
     */
    public function getNeedsEncoding()
    {
        $types = array('binary', 'date', 'datetime', 'boolean', 'float', 'integer');
        return !in_array($type, $types);
    }

    public function getDecorations($includeTypes = true)
    {
        $decorations = array();
        if ($type == 'binary') {
            $decorations['encoding'] = 'base64';
        }

        if ($includeTypes && $type != 'string') {
            $decorations['type'] = $type;
        }

        if ($value === null) {
            $decorations['null'] = $type;
        }

        return $decorations;
    }


    // Protected

    protected function _computeType()
    {
        if (array_key_exists($this->_name, $this->_record->getSerializedAttributes())) {
            $type = 'yaml';
        } else {
            $hash = $this->_record->columnHash();
            $type = $hash[$this->_name]->getType();
        }

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
        $value = $record->{$this->_name}();
        
        // check if we need to format the data
        // Ruby uses Procs for this.. which we can't do
        
        // if ($formatter = Hash::XML_FORMATTING[type.to_s])
        //   return $value ? $formatter->call($value) : null;
        // } else {
        return $value;
        // }
    }
}
