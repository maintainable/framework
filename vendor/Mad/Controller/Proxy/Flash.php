<?php
/**
 * @category   Mad
 * @package    Mad_Controller
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * Proxy accessor for flash data in request and response objects.
 *
 * @category   Mad
 * @package    Mad_Controller
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */ 
class Mad_Controller_Proxy_Flash implements ArrayAccess
{
    public function __construct($request, $response)
    {
        $this->_request = $request;
        $this->_response = $response;
    }

    public function now($offset, $value)
    {
        $this->_request->setFlash($offset, $value);
    }

    public function get($offset, $default)
    {
        return $this->_request->getFlash($offset, $default);
    }

    /** @todo hack */
    public function offsetExists($offset)
    {
        return ($this->_request->getFlash($offset) !== null);
    }

    public function offsetGet($offset)
    {
        return $this->_request->getFlash($offset);
    }
    
    public function offsetSet($offset, $value)
    {
        $this->_request->setFlash($offset, $value);
        $this->_response->setFlash($offset, $value);
        return $value;
    }
    
    public function offsetUnset($offset)
    {
        $this->_request->setFlash($offset, null);
        $this->_response->setFlash($offset, null);
    }
}