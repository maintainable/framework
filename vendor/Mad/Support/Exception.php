<?php
/**
 * @category   Mad
 * @package    Support
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * @category   Mad
 * @package    Support
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Support_Exception extends Exception 
{
    /**
     * Class Constructor
     *
     * @param string  $message
     * @param integer $code
     */
    public function __construct($message = '', $code = 0) 
    {
        parent::__construct($message, $code);
    }    
}
