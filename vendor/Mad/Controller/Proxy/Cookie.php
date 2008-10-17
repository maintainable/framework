<?php
/**
 * @category   Mad
 * @package    Mad_Controller
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * Proxy accessor for session data in request and response objects.
 *
 * @category   Mad
 * @package    Mad_Controller
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */ 
class Mad_Controller_Proxy_Cookie implements ArrayAccess
{
    public function __construct($request, $response)
    {
        $this->_request = $request;
        $this->_response = $response;
    }

    public function get($offset, $default)
    {
        return $this->_request->getCookie($offset, $default);
    }

    /** @todo hack */
    public function offsetExists($offset)
    {
        return ($this->_request->getCookie($offset) !== null);
    }

    public function offsetGet($offset)
    {
        return $this->_request->getCookie($offset);
    }
    
    /**
     * @todo need to revisit this.  the request object has no method
     * to change the value of a cookie.  that makes sense but if a 
     * cookie is changed, its value is updated in the response but 
     * not the request.  this means that reading it again later
     * in the action will return the wrong result.
     */
    public function offsetSet($offset, $value)
    {
        // $this->_request->setCookie($offset, $value);
        $this->_response->setCookie($offset, $value);
        return $value;
    }
    
    /**
     * @todo need to revisit this.  the request object has no method
     * to change the value of a cookie.  that makes sense but if a 
     * cookie is changed, its value is updated in the response but 
     * not the request.  this means that reading it again later
     * in the action will return the wrong result.
     */
    public function offsetUnset($offset)
    {
        // $this->_request->setCookie($offset, null);
        $this->_response->setCookie($offset, null);
    }
}