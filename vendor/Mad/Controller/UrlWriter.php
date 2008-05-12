<?php
/**
 * @category   Mad
 * @package    Mad_Controller
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * Base class for all Controller classes.
 *
 * @category   Mad
 * @package    Mad_Controller
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Controller_UrlWriter
{
    /** @var Horde_Routes_Util */
    protected $_utils;
    
    /** @var array */
    protected $_defaults;
    
    public function __construct($defaults = array())
    {
        $this->_defaults = $defaults;
        $this->_utils = Mad_Controller_Dispatcher::getInstance()->getRouteUtils();
    }

    public function urlFor($first = array(), $second = array())
    {
        // merge defaults
        if (is_array($first) && !empty($first)) {
            $first = array_merge($this->_defaults, $first);
        } elseif (!empty($second)) {
            $second = array_merge($this->_defaults, $second);
        }

        // url generation "route memory" is not useful here
        $this->_utils->mapperDict = array();

        // generate url
        return $this->_utils->urlFor($first, $second);
    }
    
}