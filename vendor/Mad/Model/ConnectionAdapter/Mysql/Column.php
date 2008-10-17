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
class Mad_Model_ConnectionAdapter_Mysql_Column extends Mad_Model_ConnectionAdapter_Abstract_Column
{
    /**
     * @var array
     */
    protected static $_hasEmptyStringDefault = array('binary', 'string', 'text');

    /**
     * @var string
     */
    protected $_originalDefault = null;

    /**
     * Construct
     * @param   string  $name
     * @param   string  $default
     * @param   string  $sqlType
     * @param   boolean $null
     */
    public function __construct($name, $default, $sqlType=null, $null=true)
    {
        $this->_originalDefault = $default;
        parent::__construct($name, $default, $sqlType, $null);

        if ($this->_isMissingDefaultForgedAsEmptyString()) {
            $this->_default = null;
        }
    }

    /**
     * @param   string  $fieldType
     * @return  string
     */
    protected function _simplifiedType($fieldType) 
    {
        if (strstr(strtolower($fieldType), 'tinyint(1)')) {
            return 'boolean';
        } elseif (preg_match('/enum/i', $fieldType)) {
            return 'string';
        }
        return parent::_simplifiedType($fieldType);
    }

    /**
     * MySQL misreports NOT NULL column default when none is given.
     * We can't detect this for columns which may have a legitimate ''
     * default (string, text, binary) but we can for others (integer,
     * datetime, boolean, and the rest).
     *
     * Test whether the column has default '', is not null, and is not
     * a type allowing default ''.
     * 
     * @return  boolean
     */
    protected function _isMissingDefaultForgedAsEmptyString()
    {
        return !$this->_null && $this->_originalDefault == '' && 
               !in_array($this->_type, self::$_hasEmptyStringDefault);
    }
}