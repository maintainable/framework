<?php
/**
 * @category   Mad
 * @package    Mad_Support
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * No notices or errors from PHP are ever acceptable in our
 * applications.  This error handler is registered with PHP
 * and throws all notices and errors from PHP as exceptions.  
 * This allows us to report and handle them the same as all
 * other exceptions used by the framework.
 *
 * @category   Mad
 * @package    Mad_Support
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Support_PhpErrorHandler
{
    /**
     * Install the error handler.
     */
    public static function install()
    {
        $callback = array('Mad_Support_PhpErrorHandler', 'handle');
		set_error_handler($callback);
    }
    
	/**
     * Handle a PHP error by throwing it as an exception.
	 *
	 * @param  integer  $errno    Error number
	 * @param  string   $errstr   Message describing the error
	 * @param  string   $errfile  Path to file where error occurred
	 * @param  integer  $errline  Line number where error occurred in file
	 * @return void
	 * @throws Mad_Support_Exception
	 */
	public static function handle($errno, $errstr, $errfile, $errline)
	{
	    if (ini_get('error_reporting') == 0) {
            // silence operator ("@") was used
	        return;
	    }
	    
	    throw new Mad_Support_PhpError($errstr, $errno, $errfile, $errline);
	}

}
