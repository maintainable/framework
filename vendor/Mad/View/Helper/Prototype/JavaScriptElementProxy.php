<?php
/**
 * Provides methods for converting a numbers into formatted strings.
 * Methods are provided for phone numbers, currency, percentage,
 * precision, positional notation, and file size.
 *
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * Provides methods for converting a numbers into formatted strings.
 * Methods are provided for phone numbers, currency, percentage,
 * precision, positional notation, and file size.
 *
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_View_Helper_Prototype_JavaScriptElementProxy
                        extends Mad_View_Helper_Prototype_JavaScriptProxy
                        implements ArrayAccess
{
    protected $_id;
    
    public function __construct($generator, $id)
    {
        $this->_id = $id;

        $id = $generator->view->jsonEncode($id);
        $id = '$(' . $id . ')';
        
        parent::__construct($generator, $id);
    }

    public function offsetGet($attribute)
    {
        $this->appendToFunctionChain($attribute);
        return $this;
    }

    public function offsetSet($variable, $value)
    {
        $this->assign($variable, $value);
    }

    public function offsetUnset($attribute)
    {
    }

    public function offsetExists($attribute)
    {
    }


}
