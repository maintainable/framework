<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage ConnectionAdapter
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage ConnectionAdapter
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Model_ConnectionAdapter_Abstract_Column
{
    protected $_name      = null;
    protected $_type      = null; 
    protected $_null      = null;
    protected $_primary   = null;
    protected $_limit     = null; 
    protected $_precision = null; 
    protected $_scale     = null; 
    protected $_default   = null;
    protected $_sqlType   = null;
    protected $_isText    = null;
    protected $_isNumber  = null;


    /*##########################################################################
    # Construct/Destruct
    ##########################################################################*/

    /**
     * Construct
     * @param   string  $name
     * @param   string  $default
     * @param   string  $sqlType
     * @param   boolean $null
     */
    public function __construct($name, $default, $sqlType=null, $null=true) 
    {
        $this->_name      = $name;
        $this->_null      = $null;
        $this->_sqlType   = $sqlType;
        $this->_default   = $this->typeCast($default);
        $this->_limit     = $this->_extractLimit($sqlType); 
        $this->_precision = $this->_extractPrecision($sqlType); 
        $this->_scale     = $this->_extractScale($sqlType); 
        $this->_type      = $this->_simplifiedType($sqlType);
        $this->_primary   = false;
        $this->_isText    = $this->_type == 'text'  || $this->_type == 'string';
        $this->_isNumber  = $this->_type == 'float' || $this->_type == 'integer';
    }

    /**
     * @return  boolean
     */
    public function isText()
    {
        return $this->_isText;
    }

    /**
     * @return  boolean
     */
    public function isNumber()
    {
        return $this->_isNumber;
    }

    /**
     * Casts value (which is a String) to an appropriate instance.
     */
    public function typeCast($value)
    {
        if ($value === null) return null;

        switch ($this->_type) {
            case 'integer': 
                return (int)$value;
            case 'float': 
                return (float)$value;
            case 'binary': 
                return self::binaryToString($value);
            case 'boolean': 
                return self::valueToBoolean($value);
            default: 
                return $value;
        }
    }

    /**
     * Returns the human name of the column name.
     *
     * ===== Examples
     *  Column.new('sales_stage', ...).human_name #=> 'Sales stage'     
    */
    public function getHumanName()
    {
        return Mad_Support_Inflector::humanize($this->_name);
    }


    /*##########################################################################
    # Accessor
    ##########################################################################*/

    /**
     * @return  string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @return  string
     */
    public function getDefault()
    {
        return $this->_default;
    }

    /**
     * @return  string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * @return  int
     */
    public function getLimit()
    {
        return $this->_limit;
    }

    /**
     * @return  int
     */
    public function precision()
    {
        return $this->_precision;
    }

    /**
     * @return  int
     */
    public function scale()
    {
        return $this->_scale;
    }

    /**
     * @return  boolean
     */
    public function isNull()
    {
        return $this->_null;
    }

    /**
     * @return  string
     */
    public function getSqlType()
    {
        return $this->_sqlType;
    }

    /**
     * @return  mixed
     */
    public function isPrimary()
    {
        return $this->_primary;
    }

    /**
     * @param   boolean
     */
    public function setPrimary($primary)
    {
        $this->_primary = $primary;
    }


    /*##########################################################################
    # Static
    ##########################################################################*/

    /**
     * Used to convert from Strings to BLOBs
     * 
     * @return  string
     */
    public static function stringToBinary($value)
    {
        return $value;
    }
    
    /**
     * Used to convert from BLOBs to Strings
     * 
     * @return  string
     */
    public static function binaryToString($value)
    {
        return $value;
    }

    /**
     * @param   string  $string
     * @return  string
     */
    public static function stringToTime($string)
    {
        return date('Y-m-d H:i:s', strtotime($string)); 
    }

    /**
     * @param   mixed  $value
     * @return  boolean
     */
    public static function valueToBoolean($value)
    {
        if ($value === true || $value === false) {
            return $value;
        }

        $value = strtolower($value);
        return $value == 'true' || $value == 't' || $value == '1';
    }


    /*##########################################################################
    # Protected
    ##########################################################################*/

    /**
     * @param   string  $sqlType
     * @return  int
     */
    protected function _extractLimit($sqlType)
    {
        if (preg_match("/\((.*)\)/", $sqlType, $matches)) {
            return (int)$matches[1];
        }
        return null;
    }

    /**
     * @param   string  $sqlType
     * @return  int
     */
    protected function _extractPrecision($sqlType) 
    {
        if (preg_match("/^(numeric|decimal|number)\((\d+)(,\d+)?\)/i", $sqlType, $matches)) {
            return (int)$matches[2];
        }
        return null;
    }

    /**
     * @param   string  $sqlType
     * @return  int
     */
    protected function _extractScale($sqlType) 
    {
        switch (true) {
            case preg_match("/^(numeric|decimal|number)\((\d+)\)/i", $sqlType):
                return 0;
            case preg_match("/^(numeric|decimal|number)\((\d+)(,(\d+))\)/i", 
                            $sqlType, $match):
                return (int)$match[4];
        }
    }

    /**
     * @param   string  $fieldType
     * @return  string
     */
    protected function _simplifiedType($fieldType)
    {
        switch (true) {
            case preg_match('/int/i', $fieldType): 
                return 'integer'; 
            case preg_match('/float|double/i', $fieldType): 
                return 'float';
            case preg_match('/decimal|numeric|number/i', $fieldType): 
                return $this->_scale == 0 ? 'integer' : 'decimal';
            case preg_match('/datetime/i', $fieldType): 
                return 'datetime';
            case preg_match('/timestamp/i', $fieldType): 
                return 'timestamp';
            case preg_match('/time/i', $fieldType): 
                return 'time';
            case preg_match('/date/i', $fieldType): 
                return 'date';
            case preg_match('/clob|text/i', $fieldType): 
                return 'text';
            case preg_match('/blob|binary/i', $fieldType): 
                return 'binary';
            case preg_match('/char|string/i', $fieldType): 
                return 'string';
            case preg_match('/boolean/i', $fieldType): 
                return 'boolean';
        }
    }
}