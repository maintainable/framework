<?php
/**
 * Measures the execution time of a block in a template and reports the result to the log.
 *
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * Measures the execution time of a block in a template 
 * and reports the result to the log.  Example:
 *
 *  <? $bench = $this->benchmark("Notes section") ?>
 *    <?= $this->expensiveNotesOperation() ?>
 *  <? $bench->end() ?>
 *
 * Will add something like "Notes section (0.34523)" to the log.
 *
 * You may give an optional logger level as the second argument
 * ('debug', 'info', 'warn', 'error').  The default is 'info'.
 * The level may also be given as a Horde_Log::* constant.
 *
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_View_Helper_Benchmark extends Mad_View_Helper_Base
{
    /**
     * Start a new benchmark.
     *
     * @param string          $message  Message to log after the benchmark has ended
     * @param string|integer  $level    Log level to log after the benchmark has ended
     * @return Mad_View_Helper_Benchmark_Timer
     */  
    public function benchmark($message = 'Benchmarking', $level = 'info') 
    {
        return new Mad_View_Helper_Benchmark_Timer($message, $level, $this->_view->logger);
    }
}
