<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * The base object from which all DataObjects are extended from
 *
 * @category   Mad
 * @package    Mad_Model
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Model_Errors extends Mad_Support_ArrayObject implements Iterator
{
    public static $defaultErrorMessages = array(
        'inclusion'            => "is not included in the list",
        'exclusion'            => "is reserved",
        'invalid'              => "is invalid",
        'confirmation'         => "doesn't match confirmation",
        'accepted'             => "must be accepted",
        'empty'                => "can't be empty",
        'blank'                => "can't be blank",
        'tooLong'              => "is too long (maximum is %d characters)",
        'tooShort'             => "is too short (minimum is %d characters)",
        'wrongLength'          => "is the wrong length (should be %d characters)",
        'taken'                => "has already been taken",
        'notNumber'            => "is not a number",
        'greaterThan'          => "must be greater than %d",
        'greaterThanOrEqualTo' => "must be greater than or equal to %d",
        'equalTo'              => "must be equal to %d",
        'lessThan'             => "must be less than %d",
        'lessThanOrEqualTo'    => "must be less than or equal to %d",
        'odd'                  => "must be odd",
        'even'                 => "must be even"
    );

    /**
     * @var array
     */
    protected $_errors  = array();

    /**
     * The position in the iterator over the objects.
     * @var int
     */
    protected $_position = 0;

    /**
     * @var Mad_Model_Base
     */
    protected $_base = null;


    /*##########################################################################
    # Construct
    ##########################################################################*/

    /**
     * @param   Mad_Model_Base
     */
    public function __construct($base)
    {
        $this->_base = $base;
    }

    public function __toString()
    {
        return join(', ', $this->fullMessages());
    }


    /*##########################################################################
    # Public methods
    ##########################################################################*/

    /**
     * Add an error to the base object
     * 
     * @param   string  $msg
     */
    public function addToBase($msg)
    {
        $this->add('base', $msg);
    }

    /**
     * Add an error to an attribute
     * 
     * @param   string  $attr
     * @param   string  $msg
     */
    public function add($attr, $msg=null)
    {
        if (empty($msg)) {
            $msg = self::$defaultErrorMessages['invalid'];
        }
        $this->_errors[$attr][] = $msg;
    }

    /**
     * Check if the given attribute is valid (has no errors)
     * 
     * @return  boolean
     */
    public function isInvalid($attr)
    {
        return !empty($this->_errors[$attr]);
    }

    /**
     * Get errors on the given attribute
     * 
     * @param   string  $attr
     * @return  array
     */
    public function on($attr) 
    {
        return isset($this->_errors[$attr]) ? $this->_errors[$attr] : array();
    }

    /**
     * Return errors on the base object
     * 
     * @return  array
     */
    public function onBase()
    {
        return $this->on('base');
    }

    /**
     * Returns all the full error messages in an array.
     * 
     * @return  array
     */
    public function fullMessages() 
    {
        $fullMessages = array();
        foreach ($this->_errors as $attr => $messages) {
            foreach ($messages as $msg) { 
                if ($attr != 'base') {
                    $msg = $this->_base->humanAttributeName($attr).' '.$msg;
                }
                $fullMessages[] = $msg; 
            }
        }
        return $fullMessages;
    }

    /**
     * Clear errors
     */
    public function clear()
    {
        $this->_errors = array();
        $this->rewind();
    }

    /**
     * Check if there are any errors
     * 
     * @return  boolean
     */
    public function isEmpty()
    {
        return empty($this->_errors);
    }

    /**
     * Convert errors to xml
     * 
     * @return  string
     */
    public function toXml($options = array())
    {
        if (!isset($options['root']))   { $options['root']   = 'errors'; }
        if (!isset($options['indent'])) { $options['indent'] = 2; }

        if (!empty($options['builder'])) {
            $builder = $options['builder'];
        } else {
            $builder = new Mad_Model_Serializer_Builder(array('indent' => $options['indent']));
        }
        if (empty($options['skipInstruct'])) { $builder->instruct(); }

        $tag = $builder->startTag($options['root']);
            foreach ($this->fullMessages() as $msg) {
                $tag->tag('error', $msg);
            }
        $tag->end();

        return (string)$builder;
    }


    /*##########################################################################
    # Countable implementation
    ##########################################################################*/

    /**
     * Count the number of errors
     * 
     * @return int
     */
    public function count()
    {
        $count = 0;
        foreach ($this->_errors as $attr => $messages) {
            $count += count($messages);
        }
        return $count;
    }


    /*##########################################################################
    # Iterator implementation
    ##########################################################################*/

    /**
     * Get the current object from the collection
     *
     * <code>
     *  <?php
     *  ...
     *  current($errors);
     *  ...
     *  ?>
     * </code>
     * 
     * @return  Error
     */
    public function current()
    {
        $i = 0;
        foreach ($this->_errors as $attr => $messages) {
            foreach ($messages as $msg) {
                if ($i == $this->_position) return $msg;
                $i++;
            }
        }
        return null;
    }

    /**
     * Get the current position in the Collection
     *
     * <code>
     *  <?php
     *  ...
     *  key($errors);
     *  ...
     *  ?>
     * </code>
     *
     * @return  int
     */
    public function key()
    {
        $i = 0;
        foreach ($this->_errors as $attr => $messages) {
            foreach ($messages as $msg) {
                if ($i == $this->_position) return $attr;
                $i++;
            }
        }
    }

    /**
     * Get the next element on the Collection
     *
     * <code>
     *  <?php
     *  ...
     *  next($errors);
     *  ...
     *  ?>
     * </code>
     *
     * @return  Error
     */
    public function next()
    {
        $result = $this->current();
        if ($result) $this->_position++;
        return $result;
    }

    /**
     * Rewind collection to first element
     *
     * <code>
     *  <?php
     *  ...
     *  rewind($errors);
     *  ...
     *  ?>
     * </code>
     *
     */
    public function rewind()
    {
        $this->_position = 0;
    }

    /**
     * Check if the current element exists
     * 
     * @return  boolean
     */
    public function valid()
    {
        $i = 0;
        foreach ($this->_errors as $attr => $messages) {
            foreach ($messages as $msg) {
                if ($i == $this->_position) return true;
                $i++;
            }
        }
        return false;
    }
}