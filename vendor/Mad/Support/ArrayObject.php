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
}
