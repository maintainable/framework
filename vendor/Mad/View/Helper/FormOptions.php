<?php
/**
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_View_Helper_FormOptions extends Mad_View_Helper_Base
{
    private $_instanceTag = 'Mad_View_Helper_Form_InstanceTag_FormOptions';

    public function select($objectName, $method, $choices, 
                           $options = array(), $htmlOptions = array())
    {
        $object = isset($options['object']) ? $options['object'] : null;
        unset($options['object']);
        $tag = new $this->_instanceTag($objectName, $method, $this->_view, $object);
        return $tag->toSelectTag($choices, $options, $htmlOptions);
    }

    public function collectionSelect($objectName, $method, $collection, $valueMethod, $textMethod, 
                                     $options = array(), $htmlOptions = array()) 
    {
        $object = isset($options['object']) ? $options['object'] : null;
        unset($options['object']);
        $tag = new $this->_instanceTag($objectName, $method, $this->_view, $object);
        return $tag->toCollectionSelectTag($collection, $valueMethod, $textMethod,
                                           $options, $htmlOptions);
    }

    public function countrySelect($objectName, $method, $priorityCountries = null,
                                  $options = array(), $htmlOptions = array())
    {
        $object = isset($options['object']) ? $options['object'] : null;
        unset($options['object']);
        $tag = new $this->_instanceTag($objectName, $method, $this->_view, $object);
        return $tag->toCountrySelectTag($priorityCountries, $options, $htmlOptions);        
    }
    
    public function timeZoneSelect($objecttName, $method, $priorityZones = null,
                                   $options = array(), $htmlOptions = array())
    {
        $object = isset($options['object']) ? $options['object'] : null;
        unset($options['object']);
        $tag = new $this->_instanceTag($objectName, $method, $this->_view, $object);
        return $tag->toTimeZoneSelectTag($priorityZones, $options, $htmlOptions);
    }

}
