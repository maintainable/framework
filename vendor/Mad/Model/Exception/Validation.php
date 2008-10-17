<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage Validation
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage Validation
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Model_Exception_Validation extends Mad_Model_Exception
{
    protected $_messages;

    public function __construct($message, $code=0)
    {
        $this->_messages = (array) $message;
        parent::__construct($this->_messages[0], $code);
    }

    /**
     * Get the array of validation error messages
     * @return  array
     */
    public function getMessages()
    {
        return $this->_messages;
    }
}
