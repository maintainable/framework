<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage Validation
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage Validation
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
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
