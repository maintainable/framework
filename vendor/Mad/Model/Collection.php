<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * When a using a {@link Mad_Model_Base} wrapper around a result set of objects, we return
 * multiple models as a Mad_Model_Collection object which behaves just as if it were an array.
 *
 * The advantage of using this object instead of an actual array is that we don't need
 * to iterate through the entire result set to use only a subset of the results. This
 * allows us to only access the elements of the result set as needed.
 *
 * @category   Mad
 * @package    Mad_Model
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Model_Collection implements ArrayAccess, Iterator, Countable
{
    /**
     * The {@link Mad_Model_Base} class used to instantiate new objects
     * @var object
     */
    protected $_model;


    /**
     * As we access each row, we cache them in an array for later access.
     * @var array
     */
    protected $_collection  = array();

    /**
     * The position in the iterator over the objects.
     * @var int
     */
    protected $_position = 0;


    /*##########################################################################
    # Construct/Destruct
    ##########################################################################*/

    /**
     * Construct a Mad_Model_Collection from a model instance and the collection of objects which
     * is wither a QueryResult object or array of model objects.
     *
     * @param   object  $model
     * @param   object  $collection
     */
    public function __construct(Mad_Model_Base $model, $collection=null)
    {
        // Iterate over query result
        if (!current($collection) instanceof Mad_Model_Base) {
            $this->_initResults($model, $collection);

        // Use existing array
        } else {
            $this->_collection = $collection;
        }
    }

    /**
     * Loop thru and print out classname and primary key of objects
     * @return  string
     */
    public function __toString()
    {
        if (!$first = current($this->_collection)) { return 'Empty Collection'; }

        $str = get_class($first)." Collection\n";
        foreach ($this as $item) {
            $items[] = get_class($item). ': '.$item->id;
        }
        return $str . (isset($items) ? "\n  ".implode("\n  ", $items) : '');
    }


    /*##########################################################################
    # Accessors
    ##########################################################################*/
    
    public function getCollection()
    {
        return $this->_collection;
    }


    /*##########################################################################
    # Countable Interface
    ##########################################################################*/

    /**
     * Count elements in the array. This has to force load all the object into memory
     * to get the count.
     *
     * <code>
     *  <?php
     *  ...
     *  $folders->count()
     *  ...
     *
     *  // This will work with php5.1
     *  count($folders);
     *  ...
     *  ?>
     * </code>
     * @return  int
     */
    public function count()
    {
        return count($this->_collection);
    }


    /*##########################################################################
    # Iterator Interface
    ##########################################################################*/

    /**
     * Get the current object from the collection
     *
     * <code>
     *  <?php
     *  ...
     *  current($folders);
     *  ...
     *  ?>
     * </code>
     * @return  Mad_Model_Base
     */
    public function current()
    {
        return $this->offsetGet($this->_position);
    }

    /**
     * Get the current position in the Collection
     *
     * <code>
     *  <?php
     *  ...
     *  key($folders);
     *  ...
     *  ?>
     * </code>
     *
     * @return  int
     */
    public function key()
    {
        return $this->_position;
    }

    /**
     * Get the next element on the Collection
     *
     * <code>
     *  <?php
     *  ...
     *  next($folders);
     *  ...
     *  ?>
     * </code>
     *
     * @return  Mad_Model_Base
     */
    public function next()
    {
        $this->_position++;
        return $this->current();
    }

    /**
     * Rewind collection to first element
     *
     * <code>
     *  <?php
     *  ...
     *  rewind($folders);
     *  ...
     *  ?>
     * </code>
     *
     */
    public function rewind()
    {
        $this->_position = 0;
        return $this->current();
    }

    /**
     * Check if the current element exists
     * @return  boolean
     */
    public function valid()
    {
        return $this->offsetExists($this->_position);
    }


    /*##########################################################################
    # IteratorAggregate Interface
    ##########################################################################*/

    /**
     * Return the iterator of this array. This allows for a foreach construct to be used.
     *
     * <code>
     *  <?php
     *  ...
     *  foreach ($folders as $folder) {
     *      print $folder->document_count;
     *  }
     *  ...
     *  ?>
     * </code>
     *
     * @return  object  {@link ArrayIterator}
     */
    public function getIterator()
    {
        return $this;
    }


    /*##########################################################################
    # ArrayAccess Interface
    ##########################################################################*/

    /**
     * Check if the given offset exists
     * 
     * @param   int     $offset
     * @return  boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->_collection[$offset]);
    }

    /**
     * Return the element for the given offset.
     *
     * <code>
     *  <?php
     *  ...
     *  $folder2 = $folders[1];
     *  ...
     *  ?>
     * </code>
     *
     * @param   int     $offset
     * @return  Mad_Model_Base
     */
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->_collection[$offset];
        }
    }

    /**
     * Collection is readonly, so this is not allowed (method required by interface)
     * 
     * @param   int     $offset
     * @param   mixed   $value
     */
    public function offsetSet($offset, $value) 
    {
        // Can only add Models to the collection
        if ($value instanceof Mad_Model_Base) {
            $this->_collection[] = $value;
        }        
    }

    /**
     * Collection is readonly, so this is not allowed (method required by interface)
     * 
     * @param   int     $offset
     */
    public function offsetUnset($offset) {}


    /**
     * Initialize result set into object collection
     * 
     * @param   object  $model
     * @param   array   $results
     */
    protected function _initResults(Mad_Model_Base $model, $results)
    {
        while ($row = current($results)) {
            $this->_collection[] = $model->instantiate($row);
            next($results);
        }

    }
}
