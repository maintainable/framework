<?php
/**
 * 
 * Abstract base class for all Solar objects.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Base.php 2577 2007-07-10 19:04:11Z pmjones $
 * 
 */

/**
 * 
 * Abstract base class for all Solar objects.
 * 
 * This is the class from which almost all other Solar classes are
 * extended.  Solar_Base is relatively light, and provides ...
 * 
 * * Construction-time reading of config file options 
 *   for itself, and merging of those options with any options passed   
 *   for instantation, along with the class-defined config defaults,
 *   into the Solar_Base::$_config property.
 * 
 * * A [[Solar_Base::locale()]] convenience method to return locale strings.
 * 
 * * A [[Solar_Base::_exception()]] convenience method to generate
 *   exception objects with translated strings from the locale file
 * 
 * Note that you do not define config defaults in $_config directly; 
 * instead, you use a protected property named for the class, with an
 * underscore prefix.  For exmple, a "Vendor_Class_Name" class would 
 * define the default config array in "$_Vendor_Class_Name".  This 
 * convention lets child classes inherit parent config keys and values.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 */
abstract class Solar_Base {
    
    /**
     * 
     * Collection point for configuration values.
     * 
     * Note that you do not define config defaults in $_config directly.
     * 
     * {{code: php
     *     // DO NOT DO THIS
     *     protected $_config = array(
     *         'foo' => 'bar',
     *         'baz' => 'dib',
     *     );
     * }}
     * 
     * Instead, define config defaults in a protected property named for the
     * class, withan underscore prefix.
     * 
     * For exmple, a "Vendor_Class_Name" class would define the default 
     * config array in "$_Vendor_Class_Name".  This convention lets 
     * child classes inherit parent config keys and values.
     * 
     * {{code: php
     *     // DO THIS INSTEAD
     *     protected $_Vendor_Class_Name = array(
     *         'foo' => 'bar',
     *         'baz' => 'dib',
     *     );
     * }}
     * 
     * @var array
     * 
     */
    protected $_config = array();
    
    /**
     * 
     * Constructor.
     * 
     * If the $config param is an array, it is merged with the class
     * config array and any values from the Solar.config.php file.
     * 
     * The Solar.config.php values are inherited along class parent
     * lines; for example, all classes descending from Solar_Base use the 
     * Solar_Base config file values until overridden.
     * 
     * @param mixed $config User-defined configuration values.
     * 
     */
    public function __construct($config = null)
    {
        $parents = array_reverse(Solar::parents($this, true));
        
        if ($config === false) {
            
            // properties only, no config file
            foreach ($parents as $class) {
                $var = "_$class"; // for example, $_Solar_Test_Example
                $prop = empty($this->$var) ? null : $this->$var;
                $this->_config = array_merge(
                    // current values
                    $this->_config,
                    // override with class property config
                    (array) $prop
                );
            }
            
        } else {
            
            // merge from config file too
            foreach ($parents as $class) {
                $var = "_$class";
                $prop = empty($this->$var) ? null : $this->$var;
                $this->_config = array_merge(
                    // current values
                    $this->_config,
                    // override with class property config
                    (array) $prop,
                    // override with solar config for the class
                    Solar::config($class, null, array())
                );
            }
        
            // is construct-time config a file name?
            if (is_string($config)) {
                $config = Solar::run($config);
            }
        
            // final override with construct-time config
            $this->_config = array_merge($this->_config, (array) $config);
        }
    }
    
    /**
     * 
     * Reports the API version for this class.
     * 
     * If you don't override this method, your classes will use the same
     * API version string as the Solar package itself.
     * 
     * @return string A PHP-standard version number.
     * 
     */
    public function apiVersion()
    {
        return '@package_version@';
    }

    /**
     * 
     * Convenience method for getting a dump the whole object, or one of its
     * properties, or an external variable.
     * 
     * @param mixed $var If null, dump $this; if a string, dump $this->$var;
     * otherwise, dump $var.
     * 
     * @param string $label Label the dump output with this string.
     * 
     * @return string
     * 
     */
    public function dump($var = null, $label = null)
    {
        $obj = Solar::factory('Solar_Debug_Var');
        if (is_null($var)) {
            // clone $this and remove the parent config arrays
            $clone = clone($this);
            foreach (Solar::parents($this) as $class) {
                $key = "_$class";
                unset($clone->$key);
            }
            $obj->display($clone, $label);
        } elseif (is_string($var)) {
            // display a property
            $obj->display($this->$var, $label);
        } else {
            // display the passed variable
            $obj->display($var, $label);
        }
    }
    
    /**
     * 
     * Looks up locale strings based on a key.
     * 
     * This is a convenient shortcut for calling [[Solar::$locale]]->fetch()
     * that automatically uses the current class name.
     * 
     * You can also pass an array of replacement values.  If the `$replace`
     * array is sequential, this method will use it with vsprintf(); if the
     * array is associative, this method will replace "{:key}" with the array
     * value.
     * 
     * For example:
     * 
     * {{code: php
     *     $page  = 2;
     *     $pages = 10;
     *     
     *     // given a locale string TEXT_PAGES => 'Page %d of %d'
     *     $replace = array($page, $pages);
     *     return $this->locale('Solar_Example', 'TEXT_PAGES',
     *         $pages, $replace);
     *     // returns "Page 2 of 10"
     *     
     *     // given a locale string TEXT_PAGES => 'Page {:page} of {:pages}'
     *     $replace = array('page' => $page, 'pages' => $pages);
     *     return $this->locale('Solar_Example', 'TEXT_PAGES',
     *         $pages, $replace);
     *     // returns "Page 2 of 10"
     * }}
     * 
     * @param string $key The key to get a locale string for.
     * 
     * @param string $num If 1, returns a singular string; otherwise, returns
     * a plural string (if one exists).
     * 
     * @param array $replace An array of replacement values for the string.
     * 
     * @return string The locale string, or the original $key if no
     * string found.
     * 
     * @see Manual::Solar/Using_locales
     * 
     * @see Solar::$locale
     * 
     * @see Class::Solar_Locale
     * 
     */
    public function locale($key, $num = 1, $replace = null)
    {
        static $class;
        if (! $class) {
            $class = get_class($this);
        }
        
        return Solar::$locale->fetch($class, $key, $num, $replace);
    }
    
    /**
     * 
     * Convenience method for returning exceptions with localized text.
     * 
     * @param string $code The error code; does additional duty as the
     * locale string key and the exception class name suffix.
     * 
     * @param array $info An array of error-specific data.
     * 
     * @return Solar_Exception An instanceof Solar_Exception.
     * 
     */
    protected function _exception($code, $info = array())
    {
        static $class;
        if (! $class) {
            $class = get_class($this);
        }
        
        return Solar::exception(
            $class,
            $code,
            Solar::$locale->fetch($class, $code, 1, $info),
            (array) $info
        );
    }
}
