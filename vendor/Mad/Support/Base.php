<?php
/**
 * @category   Mad
 * @package    Mad_Support
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * @category   Mad
 * @package    Mad_Support
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Support_Base
{
    /**
     * Initalization routines required by the framework.
     */
    public static function initialize()
    {
        Mad_Model_Stream::install();
        Mad_View_Stream::install();
        Mad_Support_PhpErrorHandler::install();
    }

    /**
     * Encapsulates functionality needed for __autoload()
     *
     * @params  string  $class  Class name
     */
    public static function autoload($class)
    {
        $filepath = str_replace('_', '/', $class).".php";

        // filter models through Mad_Model_Stream
        if (self::modelExists($class)) {
            $filepath = "madmodel://".MAD_ROOT."/app/models/$filepath";
        }
        require_once $filepath;
    }

    /**
     * Check if a model exists
     * @param   string  $class
     */
    public static function modelExists($class)
    {
        $filepath  = str_replace('_', '/', $class).".php";
        $modelPath = MAD_ROOT."/app/models/$filepath";

        return !strstr($class, 'Mad_') && file_exists($modelPath);
    }
    
    /**
     * Validate list of keys in the hash
     * 
     * @param   array   $hash
     * @param   array   $validKeys
     * @throws  InvalidArgumentException
     */
    public static function assertValidKeys($hash, $validKeys)
    {
        // $hash must be an array
        if (! is_array($hash)) {
            $msg = 'Expected array, got ' . gettype($hash);
            throw new InvalidArgumentException($msg);
        }
        
        // normalize validation keys so that we can use both key/associative arrays
        foreach ($validKeys as $key=>$val) {
            is_int($key) ? $valids[$val] = null : $valids[$key] = $val;
        }

        // check for invalid keys
        foreach ($hash as $key => $value) {
            if (!in_array($key, array_keys($valids))) {
                $unknown[] = $key;
            }
        }
        if (!empty($unknown)) {
            $msg = 'Unknown key(s): '.implode(', ', $unknown);
            throw new InvalidArgumentException($msg);
        }

        // add default values for any valid keys that are empty
        foreach ($valids as $key=>$value) {
            if (!isset($hash[$key])) { $hash[$key] = $value; }
        }
        return $hash;
    }
	
	/**
	 * No notices or errors from PHP are ever acceptable in our
	 * applications.  This error handler is registered with PHP
	 * and throws all notices and errors from PHP as exceptions.  
	 * This allows us to report and handle them the same as all
	 * other exceptions used by the framework.
	 *
	 * @param  integer  $errno    Error number
	 * @param  string   $errstr   Message describing the error
	 * @param  string   $errfile  Path to file where error occurred
	 * @param  integer  $errline  Line number where error occurred in file
	 * @return void
	 * @throws Mad_Support_Exception
	 */
	public static function errorHandler($errno, $errstr, $errfile, $errline)
	{
	    if (ini_get('error_reporting') == 0) {
            // silence operator ("@") was used
	        return;
	    }
	    
	    $message = "(PHP Error) $errstr in $errfile:$errline";
        throw new Mad_Support_Exception($message, $errno);
	}
    
    public static function chop($str)
    {
        if (strlen($str)) {
            if (substr($str, -2, 2) == "\r\n") {
                $str = substr($str, 0, strlen($str)-2);
            } else {
                $str = substr($str, 0, strlen($str)-1);
            } 
        }
        return $str;
    }
    
    public static function chopToNull($str)
    {
        $str = self::chop($str);
        
        if (! strlen($str)) {
            $str = null;
        }
        return $str;
    }
    
}
