<?php
/**
 * @category   Mad
 * @package    Support
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * This class is a simple way of creating timed events. The simplest way of 
 *  using it is for a single timed event.
 * 
 * <code>
 *  $t = new Mad_Support_Timer();
 *  $t->start();
 *  $elapsed = $t->finish();
 * </code>
 *
 * @category   Mad
 * @package    Support
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Support_Timer
{
    protected $_start;
    protected $_finish;
    protected $_elapsed;

    /**
     * @param   string  $name
     * @param   array   $info
     */
    public function __construct() {}

    /**
     * Start the timer
     * @return  array
     */
    public function start()
    {
        return $this->_start = gettimeofday();
    }

    /**
     * Get the elapsed time from when we called start()
     * 
     * @return  float
     */
    public function finish()
    {
        if (!$this->_start) {
            throw new Mad_Support_Exception('The timer for the name was never started');
        }
        $finish = gettimeofday();
        return (($finish['sec'] - $this->_start['sec']) * 1000)
             + (($finish['usec'] - $this->_start['usec']) / 1000);
    }
}
