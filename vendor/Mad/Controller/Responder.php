<?php
/**
 * @category   Mad
 * @package    Mad_Controller
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * @category   Mad
 * @package    Mad_Controller
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Controller_Responder
{
    /** @var Mad_Controller_Request_Http */
    protected $_request;

    /**
     * Preferred format of the requestor
     * @var string
     */
    protected $_format = 'html';

    /**
     * Class constructor
     *
     * @param  Mad_Controller_Request_Http  $request
     */
    public function __construct($request)
    {
        $this->_request = $request;
        $this->_format  = (string)$this->_request->getFormat();
    }

    /**
     * Is $format the preferred format accepted by the requestor?
     *
     * @param  string   $format  
     * @return boolean  Accepted?
     */
    public function __get($format)
    {
        return $this->_format == $format || $this->_format == 'all';
    }

    /**
     * Error message for a common mistake.
     *
     * @param  string  $method  Method name
     * @param  array   $args    Method arguments
     * @throws BadMethodCallException
     */
    public function __call($method, $args)
    {
        $msg = "Responder got undefined method as in \$wants->{$method}(), "
             . "perhaps you meant \$wants->{$method} ?";

        throw new BadMethodCallException($msg);
    }
}