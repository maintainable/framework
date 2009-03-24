<?php
/**
 * @category   Mad
 * @package    Support
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * @category   Mad
 * @package    Support
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Support_Exception extends Exception 
{
    /**
     * Class Constructor
     *
     * @param string  $message
     * @param integer $code
     * @param string  $file
     * @param string  $line
     */
    public function __construct($message = '', $code = 0, 
                                $file = null, $line = null) 
    {
        parent::__construct($message, $code);

        if ($file !== null) { $this->file = $file; }
        if ($line !== null) { $this->line = $line; }
    }
    
    /**
     * Get a title of for this exception suitable for
     * displaying to the user on an error page.
     *
     * @return string
     */
    public function getTitle()
    {
        return get_class($this);
    }    

    /**
     */
    public function getDoctoredTrace()
    {
        return $this->getTrace();
    }

}
