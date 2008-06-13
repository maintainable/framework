<?php
/**
 * @category   Mad
 * @package    Mad_Support
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * Wraps an array to provide some convenience features.
 * Method names were inspired by Python's dict type but it
 * is behaviorally different in the handling of nonexistant
 * offsets (no KeyError) and defaults.
 *
 * @category   Mad
 * @package    Mad_Support
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Support_ArrayObject extends ArrayObject
{
    /**
     * Get the value at $offset, or NULL if $offset does
     * not exist.  This is quite different from the normal
     * ArrayObject behavior and more like Ruby.
     *
     * @param  string  $offset  Offset to retrieve
     * @return mixed            Value at offset, or NULL
     */
    public function offsetGet($offset) {
        return $this->offsetExists($offset) ? parent::offsetGet($offset) : null;
    }
    
    /**
     * Get the value at $offset.  If the $offset does not
     * exist /or/ the value at $offset is NULL, then the
     * $default is returned.  The array is unaffected.
     *
     * @param  string  $offset   Offset to retrieve
     * @param  mixed   $default  Default value
     * @return mixed             Value at offset or default
     */
    public function get($offset, $default = null)
    {
        $value = $this->offsetGet($offset);
        return isset($value) ? $value : $default;
    }
    
    /**
     * Gets the value at $offset.  If no value exists at
     * that offset, or the value $offset is NULL, then 
     * the $default is inserted into the array.
     *
     * As with 
     *
     * @param  string  $offset   Offset to retrieve or set
     * @param  string  $default  Default value
     * @return mixed             Value at offset or default
     */
    public function setDefault($offset, $default = null)
    {
        if ($this->offsetGet($offset) === null) {
            $this->offsetSet($offset, $default);
        }
        return $this->offsetGet($offset);
    }

    /**
     * Gets the value at $offset and deletes it from the
     * array.  If no value exists at $offset, or the value
     * at $offset is NULL, then the $default will be returned.
     *
     * @param  string  $offset   Offset to pop
     * @param  string  $default  Default value
     * @return mixed             Value at offset or default
     */
    public function pop($offset, $default = null)
    {
        if ($this->offsetExists($offset)) {
            $value = $this->offsetGet($offset);
            $this->offsetUnset($offset);
        } else {
            $value = $default;
        }
        return isset($value) ? $value : $default;
    }

    /**
     * Updates this array with the contents of $array.
     *
     * @param  array|Traversable  $array  Array to merge into this one
     * @return void
     */ 
    public function update($array)
    {
        if (! is_array($array) && ! $array instanceof Traversable) {
            $msg = 'expected array or traversable, got ' . gettype($array);
            throw new InvalidArgumentException($msg);
        }
        
        foreach ($array as $k => $v) {
            $this->offsetSet($k, $v);
        }
    }

    /**
     * Clear the array, erasing all of its contents.
     *
     * @return void
     */
    public function clear()
    {
        $this->exchangeArray(array());
    }
    
    /**
     * Returns True if a value exists at $offset, False otherwise.
     * @see offsetExists().
     *
     * @param  string  $offset  Offset to check
     * @return boolean          Offset exists? 
     */
    public function hasKey($offset)
    {
       return $this->offsetExists($offset);
    }
    
    /**
     * Gets an array containing the keys of this array.
     *
     * @return  array  Array of keys
     */
    public function getKeys()
    {
        $keys = array();
        foreach ($this->getIterator() as $k => $v) { $keys[] = $k; }
        return $keys;
    }
    
    /**
     * Gets an array containing the values of this array.
     *
     * @return  array  Array of values
     */
    public function getValues()
    {
        $values = array();
        foreach ($this->getIterator() as $k => $v) { $values[] = $v; }
        return $values;
    }
    
    /**
     * Convert the array to XML
     */
    public function toXml($options = array())
    {
        $firstElt  = $this->get(0);
        $firstType = is_object($firstElt) ? get_class($firstElt) : gettype($firstElt);
        $sameTypes = true;

        foreach ($this as $element) {
            // either an array or object with toXml method
            if (!is_array($element) && !is_callable(array($element, 'toXml'))) {
                throw new Mad_Support_Exception("Not all elements respond to toXml");
            }
            if (get_class($element) != $firstType) { $sameTypes = false; }
        }

        if (!isset($options['root'])) {
            if ($sameTypes && $this->count() > 0) {
                $options['root'] = Mad_Support_Inflector::pluralize($firstType);
            } else {
                $options['root'] = 'records';
            }
        }
        if (!isset($options['children'])) {
            $options['children'] = Mad_Support_Inflector::singularize($options['root']);
        }

        if (!isset($options['indent']))    { $options['indent']    = 2; }
        if (!isset($options['skipTypes'])) { $options['skipTypes'] = false; }

        if (empty($options['builder'])) {
            $options['builder'] = new Mad_Support_Builder(
                array('indent' => $options['indent']));
        }

        $root = $options['root'];
        unset($options['root']);
        
        $children = $options['children'];
        unset($options['children']);

        if (!array_key_exists('dasherize', $options) || !empty($options['dasherize'])) {
            $root = Mad_Support_Inflector::dasherize($root);
        }
        if (empty($options['skipInstruct'])) {
            $options['builder']->instruct(); 
        }

        $opts = array_merge($options, array('root' => $children));

        $builder = $options['builder'];
        $attrs   = $options['skipTypes'] ? array() : array('type' => 'array');
        
        // no elements in array
        if ($this->count() == 0) {
            $builder->tag($root, '', $attrs);
            
        // build xml from elements
        } else {
            $tag = $builder->startTag($root, '', $attrs);
                $opts['skipInstruct'] = true;
                foreach ($this as $element) {
                    // associative array
                    if (is_array($element) && !is_int(key($element))) {
                        $conversion = new Mad_Support_Conversion;
                        $conversion->hashToXml($element, $opts);
                    // array
                    } elseif (is_array($element)) {
                        $ao = new Mad_Support_ArrayObject($element);
                        $ao->toXml($opts);
                    // object
                    } else {
                        $element->toXml($opts);
                    }
                }
            $tag->end();
        }
        return (string)$builder;
    }
}
