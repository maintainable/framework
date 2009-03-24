<?php
/**
 * An instance of this class is returned by
 * Mad_View_Helper_Capture::capture().
 * 
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * An instance of this class is returned by
 * Mad_View_Helper_Capture::capture().
 *
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_View_Helper_Capture_Base
{
    /**
     * Are we currently buffering?
     *
     * @var boolean
     */
    protected $_buffering = true;

    /**
     * Start capturing.
     */
    public function __construct() {
        ob_start();
    }

    /**
     * Stop capturing and return what was captured.
     *
     * @return string
     * @throws Mad_View_Exception
     */
    public function end()
    {
        if ($this->_buffering) {
            $this->_buffering = false;
            $output = ob_get_clean();
            return $output;
        } else {
            throw new Mad_View_Exception('Capture already ended');
        }
    }

}
