<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * @category   Mad
 * @package    Mad_Model
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Model_PaginatedCollection extends Mad_Support_ArrayObject implements Iterator
{
    /**
     * The collection of objects
     * @var array
     */
    protected $_collection  = array();

    /**
     * Paging variables
     */
    public $currentPage  = null;
    public $perPage      = null;
    public $totalEntries = null;
    public $pageCount    = null;
    public $range        = null;

    /*##########################################################################
    # Construct/Destruct
    ##########################################################################*/

    /**
     * Construct a Mad_Model_PaginatedCollection from a collection of objects
     *
     * @param   array   $collection
     * @param   int     $page
     * @param   int     $perPage
     * @param   int     $total
     */
    public function __construct($collection=null, $page=1, $perPage=15, $total=0)
    {
        $this->_collection = $collection;

        $this->currentPage  = $page;
        $this->perPage      = $perPage;
        $this->totalEntries = $total;
        $this->pageCount    = ceil($this->totalEntries/$this->perPage);
        $this->_initPagingRange();
    }

    /**
     * @return  string
     */
    public function __toString()
    {
        if (!$first = $this->current()) { return 'Empty Collection'; }

        $str = get_class($first)." Collection\n";
        foreach ($this as $item) {
            $items[] = get_class($item). ': '.$item->id;
        }
        return $str . (isset($items) ? "\n  ".implode("\n  ", $items) : '');
    }


    /*##########################################################################
    # XML
    ##########################################################################*/

    /** 
     * Proxy to parent Mad_Support_ArrayObject#toXml, except that 
     * we know the explicit model type. 
     */
    public function toXml($options = array()) 
    {
        return $this->_collection->toXml($options);
    }


    /*##########################################################################
    # Countable Interface
    ##########################################################################*/

    /**
     * Count elements in the array. This has to force load all the object into memory
     * to get the count.
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
     */
    public function current()
    {
        if ($this->_collection instanceof Mad_Model_Collection) {
            return $this->_collection->current();
        } else {
            return current($this->_collection);
        }
    }

    /**
     * Get the current position in the Collection
     */
    public function key()
    {
        if ($this->_collection instanceof Mad_Model_Collection) {
            return $this->_collection->key();
        } else {
            return key($this->_collection);
        }
    }

    /**
     * Get the next element on the Collection
     */
    public function next()
    {
        if ($this->_collection instanceof Mad_Model_Collection) {
            return $this->_collection->next();
        } else {
            return next($this->_collection);
        }
    }

    /**
     * Rewind collection to first element
     */
    public function rewind()
    {
        if ($this->_collection instanceof Mad_Model_Collection) {
            return $this->_collection->rewind();
        } else {
            return reset($this->_collection);
        }
    }

    /**
     * Check if the current element exists
     * @return  boolean
     */
    public function valid()
    {
        if ($this->_collection instanceof Mad_Model_Collection) {
            return $this->_collection->valid();
        } else {
            return current($this->_collection) !== false;
        }
    }


    /*##########################################################################
    # IteratorAggregate Interface
    ##########################################################################*/

    /**
     * Return the iterator of this array. This allows for a foreach construct to be used.
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
     * @param   int     $offset
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
    public function offsetSet($offset, $value) {}

    /**
     * Collection is readonly, so this is not allowed (method required by interface)
     * 
     * @param   int     $offset
     */
    public function offsetUnset($offset) {}


    /*##########################################################################
    # Protected
    ##########################################################################*/

    /**
     * Initialize the range of items in this page. 
     * eg: 16 - 30
     */
    protected function _initPagingRange()
    {
        $first = ($this->currentPage - 1) * $this->perPage + 1;
        $last  = $first + ($this->perPage - 1);
        $last  = $last > $this->totalEntries ? $this->totalEntries : $last;
        $this->range = $this->totalEntries > 0 ? "$first - $last" : "0";
    }

}