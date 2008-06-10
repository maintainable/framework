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
        $this->_analyze();
    }

    /**
     * Is $format the preferred format accepted by the requestor?
     *
     * @param  string   $format  
     * @return boolean  Accepted?
     */
    public function __get($format) {
        return $this->_format == $format;
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

    /*
     * Very hacked together analysis of HTTP_ACCEPT and URI to
     * guess the format that the remote is expecting.
     *
     * @todo proper mime types implementation 
     */
    protected function _analyze()
    {
        $accept = $this->_request->getServer('HTTP_ACCEPT');
        $uri    = $this->_request->getUri();

        if (strstr($accept, 'text/javascript') || strstr($uri, '.js')) {
            $this->_format = 'js';
        } else if (strstr($accept, 'text/html') || strstr($uri, '.html')) {
            $this->_format = 'html';
        } else if (strstr($accept, 'text/xml') || strstr($uri, '.xml')) {
            $this->_format = 'xml';
        }
    }

}