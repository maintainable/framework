<?php
/**
 * @category   Mad
 * @package    Mad_Controller
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * Proxy accessor for session data in request and response objects.
 *
 * @category   Mad
 * @package    Mad_Controller
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */ 
class Mad_Controller_Proxy_Session implements ArrayAccess
{
    public function __construct($request, $response)
    {
        $this->_request = $request;
        $this->_response = $response;
    }

    public function get($offset, $default)
    {
        return $this->_request->getSession($offset, $default);
    }

    /** @todo hack */
    public function offsetExists($offset)
    {
        return ($this->_request->getSession($offset) !== null);
    }

    public function offsetGet($offset)
    {
        return $this->_request->getSession($offset);
    }
    
    public function offsetSet($offset, $value)
    {
        $this->_request->setSession($offset, $value);
        $this->_response->setSession($offset, $value);
        return $value;
    }
    
    public function offsetUnset($offset)
    {
        $this->_request->setSession($offset, null);
        $this->_response->setSession($offset, null);
    }

    /** @todo hack: session unset/reset is broken without this */
    public function reset()
    {
        $this->_request->setSession(array());
        $this->_response->setSession(array());

        $_SESSION = array();        
    }
}