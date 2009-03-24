<?php
/**
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * Dumps a variable for inspection.
 * Portions borrowed from Paul M. Jones' Solar_Debug
 *
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_View_Helper_Debug extends Mad_View_Helper_Base
{
    /**
     * Dumps a variable for inspection.
     *
     * @param   string  $var
     * @return  string
     */
    public function debug($var)
    {
        return '<pre class="debug_dump">'
             . htmlspecialchars($this->_fetch($var))
             . '</pre>';
    }

    /**
     * Returns formatted output from var_dump().
     * 
     * Buffers the var_dump output for a variable and applies some
     * simple formatting for readability.
     * 
     * @param  mixed   $var   variable to dump
     * @return string         formatted results of var_dump()
     */     
    private function _fetch($var)
    {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
        return $output;
    }
}
