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
    /** 
     * Defaults to merge into route parameters when not using named routes.
     * @var array 
     */
    protected $_defaults;

    /** 
     * @var Horde_Routes_Util 
     */
    protected $_utils;
    
    /**
     * Class constructor
     *
     * @param  array                   $defaults  Defaults to merge for urlFor()
     * @param  null|Horde_Route_Utils  $utils     Route utilities
     */
    public function __construct($defaults = array(), $utils = null)
    {
        $this->_defaults = $defaults;
        
        if ($utils === null) {
            $utils = Mad_Controller_Dispatcher::getInstance()->getRouteUtils();
        }
        $this->_utils = $utils;
    }

    /**
     * Generate a URL.  Same signature as Horde_Routes_Utils->urlFor().
     *
     * @param  $first   mixed
     * @param  $second  mixed
     * @return string
     */
    public function urlFor($first, $second = array())
    {
        // anonymous route: serialize to params & merge defaults
        //   urlFor(array('controller' => 'books'))
        if (is_array($first)) {
            $first = array_merge($this->_defaults, 
                                 $this->_serializeToParams($first));
        } 
        
        // named route: serialize to params only (no merge)
        //   urlFor('notes', array('action' => 'show', 'id' => 1))
        if (is_array($second)) {
            $second = $this->_serializeToParams($second);
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