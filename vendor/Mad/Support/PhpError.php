<?php
/**
 * @category   Mad
 * @package    Mad_Support
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * Exception wrapping a PHP error.
 *
 * @category   Mad
 * @package    Mad_Support
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Support_PhpError extends Mad_Support_Exception
{
    /**
     * Get a title for this exception suitable for
     * displaying to the user on an error page.
     *
     * @return string
     */
    public function getTitle()
    {
        switch ($this->code) {
            case E_WARNING:         $title = 'Warning';          break;
            case E_NOTICE:          $title = 'Notice';           break;
            case E_CORE_WARNING:    $title = 'Core Warning';     break;
            case E_COMPILE_WARNING: $title = 'Compile Warning';  break;
            case E_USER_ERROR:      $title = 'User Error';       break;
            case E_USER_WARNING:    $title = 'User Warning';     break;
            case E_USER_NOTICE:     $title = 'User Notice';      break;
            case E_STRICT:          $title = 'Strict Notice';    break;
            default:
                if (defined('E_RECOVERABLE_ERROR') && 
                                $errno == E_RECOVERABLE_ERROR) {
                    $title = 'Recoverable Error';
                } else {
                    $title = 'Unknown Error';
                }
        }
    
        return "PHP $title";
    }

    /**
     * PHP's getTrace() is declared as final so we use this method
     * to return the trace with the first frame removed.  
     *
     * The first frame always contains Mad_Support_PhpErrorHandler::handle(),
     * which is just extra noise when reading the trace.
     *
     * @return  array  
     */
    public function getDoctoredTrace()
    {
        $trace = $this->getTrace();
        array_shift($trace);
        return $trace;
    }
}