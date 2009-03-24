<?php
/**
 * @category   Mad
 * @package    Mad_Controller
 * @subpackage Response
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * Represents an HTTP response to the user.
 *
 * @category   Mad
 * @package    Mad_Controller
 * @subpackage Response
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Controller_Response_Mock extends Mad_Controller_Response_Http
{
    /**
     * Printing the response displays the body content
     */
    public function __toString()
    {
        return isset($this->_body) ? $this->_body : '';
    }

    /**
     * Allow access to cookies
     * 
     * @param   string  $name
     * @return  string
     */
    public function getCookie($name = null)
    {
        if (isset($name)) {
            return isset($this->_cookie[$name]) ? $this->_cookie[$name] : null;
        } else {
            return $this->_cookie;
        }
    }

    /**
     * Allow access to session
     * 
     * @param   string  $name
     * @return  string
     */
    public function getSession($name = null)
    {
        if (isset($name)) {
            return isset($this->_session[$name]) ? $this->_session[$name] : null;
        } else {
            return $this->_session;
        }
    }

    /**
     * Allow access to flash
     * 
     * @param   string  $name
     * @return  string
     */
    public function getFlash($name = null)
    {
        if (isset($name)) {
            return isset($this->_flash[$name]) ? $this->_flash[$name] : null;
        } else {
            return $this->_flash;
        }
    }
}
