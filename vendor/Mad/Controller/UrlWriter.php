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
    
    /**
     * Class constructor
     *
     * @param  array  $defaults  Defaults to merge in every call to urlFor().
     */
    public function __construct($defaults = array())
    {
        $this->_defaults = $defaults;
        $this->_utils = Mad_Controller_Dispatcher::getInstance()->getRouteUtils();
    }

    /**
     * Generate a URL.  Same signature as Horde_Routes_Utils->urlFor().
     *
     * @param  $first   mixed
     * @param  $second  mixed
     * @return string
     */
    public function urlFor($first = array(), $second = array())
    {
        // serialize to params & merge defaults
        if (is_array($first) && !empty($first)) {
            $first = array_merge($this->_defaults, 
                                 $this->_serializeToParams($first));

        } elseif (!empty($second)) {
            $second = array_merge($this->_defaults, 
                                  $this->_serializeToParams($second));
        }

        // url generation "route memory" is not useful here
        $this->_utils->mapperDict = array();

        // generate url
        return $this->_utils->urlFor($first, $second);
    }
    
    /**
     * Serialize any objects in the collection supporting toParam() before
     * passing the collection to Horde_Routes.
     *
     * @param  array  $collection
     * @param  array   
     */     
    protected function _serializeToParams($collection)
    {
        foreach ($collection as &$value) {
            if (is_object($value) && method_exists($value, 'toParam')) {
                $value = $value->toParam();
            }
        }
        return $collection;
    }
}