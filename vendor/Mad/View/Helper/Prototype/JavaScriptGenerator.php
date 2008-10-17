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
class Mad_View_Helper_Prototype_JavaScriptGenerator implements ArrayAccess
{
    /** @todo private */
    public $view;
    
    /** @todo private */
    public $lines = array();
    
    public function __construct($view)
    {
        $this->view = $view;
    }
    
    /** @todo rjs debug wrapper */
    public function __toString()
    {
        return implode("\n", $this->lines);
    }

    /** @todo implementation */
    public function offsetGet($id)
    {
        return new Mad_View_Helper_Prototype_JavascriptElementProxy($this, $id);
    }

    /** @todo implementation */
    public function offsetSet($name, $value)
    {}
    
    /** @todo implementation */
    public function offsetUnset($name)
    {}
    
    /** @todo implementation */
    public function offsetExists($name)
    {}
    
    public function insertHtml($position, $id) 
    {
        $optionsForRender = func_get_args();
        array_shift($optionsForRender);
        array_shift($optionsForRender);
        
        $insertion = Mad_Support_Inflector::camelize((string)$position);
        return $this->call("new Insertion.{$insertion}", 
                           $id,
                           $this->_render($optionsForRender)); 
    }
    
    public function replaceHtml($id) 
    {
        $optionsForRender = func_get_args();
        array_shift($optionsForRender);

        return $this->call("Element.update", 
                           $id, 
                           $this->_render($optionsForRender));
    }

    public function replace($id) 
    {
        $optionsForRender = func_get_args();
        array_shift($optionsForRender);
        
        return $this->call("Element.replace",
                           $id,
                           $this->_render($optionsForRender));
    }
    
    public function remove()
    {
        $args = func_get_args();
        return $this->_loopOnMultipleArgs('Element.remove', $args);
    }

    public function show()
    {
        $args = func_get_args();
        return $this->_loopOnMultipleArgs('Element.show', $args);
    }

    public function hide()
    {
        $args = func_get_args();
        return $this->_loopOnMultipleArgs('Element.hide', $args);
    }

    public function toggle()
    {
        $args = func_get_args();
        return $this->_loopOnMultipleArgs('Element.toggle', $args);
    }

    public function alert($message)
    {
        return $this->call('alert', $message);
    }

    public function redirectTo($location)
    {
        return $this->assign('window.location.href',
                             $this->view->urlFor($location));
    }

    public function call($function)
    {
        $args = func_get_args();
        array_shift($args);

        $args = $this->argumentsForCall($args);
        return $this->_record("{$function}($args)");
    }
    
    public function assign($variable, $value)
    {
        $object = $this->javascriptObjectFor($value);
        return $this->_record("$variable = $object");
    }

    public function append($javascript)
    {
        return $this->lines[] = $javascript;
    }

    /** @todo implementation */
    public function delay($seconds)
    {
    }

    public function visualEffect($name, $id = null, $options = array())
    {
        $effect = $this->view->visualEffect($name, $id, $options = array());
        return $this->_record($effect);
    }

    public function sortable($id, $options = array())
    {
        $sortable = $this->view->sortableElementJs($id, $options);
        return $this->_record($sortable);
    }
    
    public function draggable($id, $options = array())
    {
        $draggable = $this->view->draggableElementJs($id, $options);
        return $this->_record($draggable);
    }
    
    public function dropReceiving($id, $options = array())
    {
        $dropReceiving = $this->view->dropReceivingElementJs($id, $options);
        return $this->_record($dropReceiving);
    }

    private function _loopOnMultipleArgs($method, $ids)
    {
        if (count($ids) > 1) {
            $object = $this->javascriptObjectFor($ids);
            $js = "{$object}.each({$method})";
        } else {
            $id = $this->view->jsonEncode($ids[0]);
            $js = "{$method}($id)";
        }
        
        return $this->_record($js);
    }

    /** @todo private */
    public function argumentsForCall($arguments)
    {
        foreach ($arguments as &$arg) {
            $arg = $this->javascriptObjectFor($arg);
        }
        return implode(', ', $arguments);
    }

    private function _record($line)
    {
        $line = rtrim(strval($line), "\n");
        $line = preg_replace('/\;\z/', '', $line);
        return $this->append("$line;");
    }
    
    /** @todo private */
    public function javascriptObjectFor($object)
    {
        if (is_scalar($object)) {
            $js = '"' . $this->view->escapeJavascript($object) . '"';
        } else {
            $js = $this->view->jsonEncode($object);
        }
        return $js;
    }

    private function _render($optionsForRender)
    {
        if (is_array($optionsForRender[0])) {
            return $this->view->render($optionsForRender);
        } else {
            return (string)$optionsForRender[0];
        }
    }
}