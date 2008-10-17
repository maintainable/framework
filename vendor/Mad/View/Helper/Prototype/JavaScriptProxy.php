<?php
/**
 * Provides methods for converting a numbers into formatted strings.
 * Methods are provided for phone numbers, currency, percentage,
 * precision, positional notation, and file size.
 *
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * Provides methods for converting a numbers into formatted strings.
 * Methods are provided for phone numbers, currency, percentage,
 * precision, positional notation, and file size.
 *
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_View_Helper_Prototype_JavaScriptProxy
{
    protected $_generator;

    protected $_root;
    
    protected $_functionChain;
    
    public function __construct($generator, $root = null)
    {
        $this->_generator = $generator;
        
        if (isset($root)) {
            $this->_generator->append($root);
        }
    }

    public function __call($method, $args)
    {
        $method = Mad_Support_Inflector::camelize($method, 'lower');
        array_unshift($args, $method);

        return call_user_func_array(array($this, 'call'), $args);
    }

    public function call($function) 
    {
        $arguments = func_get_args();
        array_shift($arguments);

        $arguments = $this->_generator->argumentsForCall($arguments);
        $this->appendToFunctionChain("$function($arguments)");
        return $this;
    }

    public function assign($variable, $value)
    {
        $object = $this->_generator->javascriptObjectFor($value);
        $this->appendToFunctionChain("$variable = $object");
    }

    public function appendToFunctionChain($call)
    {
        $line = rtrim(end($this->_generator->lines), ';');
        $this->_generator->lines[key($this->_generator->lines)] = $line . ".{$call};";
    }
}
