<?php
/**
 * @category   Mad
 * @package    Mad_Support
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * @category   Mad
 * @package    Mad_Support
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Support_Object
{
    /**
     * List of attributes available for reading
     * @var array
     */
    protected $_attrReaders = array();

    /**
     * List of attribute available for writing
     * @var array
     */
    protected $_attrWriters = array();

    /**
     * anonymous attribute values 
     * @var array
     */
    protected $_attrValues = array();


    /**
     * Dynamically get value for an attribute. 
     *
     * @param   string  $name
     * @return  string
     * @throws  Mad_Support_Exception
     */
    public function __get($name)
    {
        // attribute-reader value
        if (in_array($name, $this->_attrReaders)) {
            return $this->_getAttribute($name);
        }
        // call overloading for subclass
        if (method_exists($this, '_get')) {
            return $this->_get($name);
        }
        throw new Mad_Support_Exception("Unrecognized attribute '$name'");
    }

    /**
     * Dynamically set value for an attribute. Attributes cannot be set once an
     * object has been destroyed. Primary Key cannot be changed if the data was
     * loaded from a database row
     *
     * @param   string  $name
     * @param   mixed   $value
     * @throws  Mad_Support_Exception
     */
    public function __set($name, $value)
    {
        // attribute-writer value
        if (in_array($name, $this->_attrWriters)) {
            return $this->_setAttribute($name, $value);
        }
        // call overloading for subclass
        if (method_exists($this, '_set')) {
            return $this->_set($name, $value);
        }
        throw new Mad_Support_Exception("Unrecognized attribute '$name'");
    }

    // check if items are set
    public function __isset($name)
    {
        // attribute-reader value
        if (in_array($name, $this->_attrReaders)) {
            $value = $this->_getAttribute($name);
            return !empty($value);
        }
        // call overloading for subclass
        if (method_exists($this, '_isset')) {
            return $this->_isset($name);
        }
    }


    /*##########################################################################
    # Attributes
    ##########################################################################*/

    /**
     * Add list of attribute readers for this object.
     *
     * Multiple readers can be set at once.
     *
     * {{code: php
     *     class User extends Mad_Model_Base 
     *     {
     *         protected $_foo = null;
     *         protected $_bar = null;
     *         protected $_baz = null;
     *         
     *         public function _initialize()
     *         {
     *             $this->attrReader('foo', 'bar', 'baz');
     *         }
     * 
     *         // this overrides retrieving default value from the property
     *         // and allows us to split the value when it is retrieved
     *         public function getFoo($name)
     *         {
     *             return explode(', ', 'foo');
     *         }
     *     }
     * }}
     * 
     * When readers are accessed, they will attempt to first
     * read a public method prefixed with `get`. If this method
     * is missing, we'll fall back to a generic hash.
     *
     * {{code: php
     *     $user = new User;
     * 
     *     // our attribute reader called "getFoo" will be executed
     *     print $user->foo;  // => 'foo'
     * 
     *     // when no proxy method is defined, we just return $_bar's value
     *     print $user->bar;  // => null
     * }}
     * 
     * @param  varargs  $attributes
     */
    public function attrReader($attributes)
    {
        $names = func_get_args();
        $this->_attrReaders = array_unique(
            array_merge($this->_attrReaders, $names));
    }

    /**
     * Add list of attribute writers for this object.
     *
     * Multiple writers can be set at once.
     *
     * {{code: php
     *     class User extends Mad_Model_Base 
     *     {
     *         protected $_foo = null;
     *         protected $_bar = null;
     *         protected $_baz = null;
     * 
     *         public function _initialize()
     *         {
     *             $this->attrWriter('foo', 'bar', 'baz');
     *             $this->attrReader('foo');
     *         }
     * 
     *         // this overrides setting default value from $_foo and 
     *         // allows us to join the array value before it is assigned
     *         public function setFoo($value)
     *         {
     *             $this->_foo = join(', ', $value);
     *         }
     *     }
     * }}
     * 
     * When writers are accessed, they will attempt to first
     * use a public method prefixed with `set`. If this method
     * is missing, we'll fall back to a generic hash.
     *
     * {{code: php
     *     // we pass in the "foo" attribute as an array
     *     $user = new User;
     *     $user->foo = array('derek', 'mike');
     *     
     *     // our attribute writer called "setFoo" to join it to a string 
     *     print $user->foo;  // => "derek, mike"
     *     
     *     // when no proxy method is defined, we just set $_bar's value
     *     $user->bar = 'test';
     * }}
     * 
     * @param  varargs  $attributes
     */
    public function attrWriter($attributes)
    {
        $names = func_get_args();
        $this->_attrWriters = array_unique(
            array_merge($this->_attrWriters, $names));
    }

    /**
     * Add list of attribute reader/writers for this object.
     *
     * Multiple accessors can be set at once.
     * 
     * {{code: php
     *     class User extends Mad_Model_Base 
     *     {
     *         protected $_foo => null;
     *         protected $_bar => null;
     * 
     *         public function _initialize()
     *         {
     *             $this->attrAccessor('foo', 'bar');
     *         }
     * 
     *         // enclose entire string in quotes
     *         public function getFoo()
     *         {
     *             return '"'.$this->_foo.'"';
     *         }
     * 
     *         // strip out commas from value
     *         public function setFoo($value)
     *         {
     *             $this->_foo = str_replace(',', '', $value);
     *         }
     *     }
     * }}
     * 
     * When accessors are accessed, they will attempt to first
     * read a public method prefixed with `get` or `set`. If these method
     * are missing, we'll fall back to the protected property value.
     *
     * {{code: php
     *     // This allows us to set/get the "foo" property 
     *     $user = new User;
     *     $user->foo = 'hey, there'
     *     
     *     print $user->foo;  // => '"hey there"'
     *     
     *     // when no proxy method is defined, we just return $_bar's value
     *     $user->bar = 'test';
     *     print $user->bar; // => 'test'
     * }}
     * 
     * @param  varargs  $attributes
     */
    public function attrAccessor($attributes)
    {
        $names = func_get_args();
        $this->_attrReaders = array_unique(
            array_merge($this->_attrReaders, $names));
        $this->_attrWriters = array_unique(
            array_merge($this->_attrWriters, $names));
    }

    /**
     * Get the value for an attribute in this object.
     * 
     * @param   string  $name
     * @return  string
     */
    protected function _getAttribute($name)
    {
        // check for reader proxy method
        $underscore = Mad_Support_Inflector::underscore("get_$name");
        $methodName = Mad_Support_Inflector::camelize($underscore, 'lower');
        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        } else {
            $property = "_$name";
            if (property_exists($this, $property)) {
                return $this->$property;
            }
            return isset($this->_attrValues[$name]) ? $this->_attrValues[$name] : null;
        }
        return null;
    }

    /**
     * Set the value for an attribute in this object.
     * 
     * @param   string  $name
     * @param   mixed   $value
     */
    protected function _setAttribute($name, $value)
    {
        // check for writer proxy method
        $underscore = Mad_Support_Inflector::underscore("set_$name");
        $methodName = Mad_Support_Inflector::camelize($underscore, 'lower');
        if (method_exists($this, $methodName)) {
            $this->$methodName($value);
        } else {
            $property = "_$name";
            if (property_exists($this, $property)) {
                $this->$property = $value;
            } else {
                $this->_attrValues[$name] = $value;                
            }
        }
    }
    
}