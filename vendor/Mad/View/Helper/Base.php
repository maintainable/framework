<?php
/**
 * Helpers are PHP methods that perform small functions related
 * to presentation.  All Helpers descend from Mad_View_Helper_Base.
 *
 * All helpers hold a link back to the instance of the view.  The
 * undefined property handlers (__get()/__call() methods) are used
 * to mix helpers together, effectively placing them on the same
 * pane of glass (the view) and allowing any helper to call any other.
 *
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * Helpers are PHP methods that perform small functions related
 * to presentation.  All Helpers descend from Mad_View_Helper_Base.
 *
 * All helpers hold a link back to the instance of the view.  The
 * undefined property handlers (__get()/__call() methods) are used
 * to mix helpers together, effectively placing them on the same
 * pane of glass (the view) and allowing any helper to call any other.
 *
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
abstract class Mad_View_Helper_Base
{
    /**
     * Holds the instance of the view using this helper
     * @var object  Mad_View_Base
     */
    protected $_view;

    /**
     * Class constructor
     */
    public function __construct($view = null)
    {
        $this->_view = $view;
    }

    /**
     * Proxy on undefined property access (get)
     */
    public function __get($name)
    {
        return $this->_view->$name;
    }

    /**
     * Proxy on undefined property access (set)
     */
    public function __set($name, $value)
    {
        return $this->_view->$name = $value;
    }

    /**
     * Proxy on undefined method calls
     */
    public function __call($name, $args)
    {
        return $this->_view->__call($name, $args);
    }

}
