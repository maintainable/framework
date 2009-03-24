<?php
/**
 * @category   Mad
 * @package    Mad_Support
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * @category   Mad
 * @package    Mad_Support
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
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
     * Is $class one of the models in the app/models directory?
     *
     * @param   string   $class  Class name, possibly a model
     * @return  boolean          Is it a model?
     */
    public static function modelExists($class)
    {
        static $classes = array();

        // build array of all classes in the app/models models
        if (empty($classes)) {
			$path = MAD_ROOT . DIRECTORY_SEPARATOR
				  . 'app' . DIRECTORY_SEPARATOR . 'models';

			$pathLen = strlen($path) + 1;
			foreach(new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator($path)) as $f) {
				if ($f->isFile() && substr($f->getFilename(), -4) == '.php') {
					// compute possible model pathname of $class
					$pathname = $f->getPathname();
					$thisClass = str_replace(DIRECTORY_SEPARATOR, '_', 
					    substr($pathname, $pathLen, -4));
					$classes[$thisClass] = true;
				}
			}
        }

        return isset($classes[$class]);
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
