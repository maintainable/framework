<?php
/**
 * @category   Mad
 * @package    Support
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * This is a proxy object for wrapping the procedural APIs 
 * provided by many PHP extensions.  
 *
 * This code:
 *   $res  = ldap_connect($host, $port);
 *   $bind = ldap_bind($res, $rdn, $pass);
 *
 * Becomes:
 *   $ldap = new Mad_Support_ExtensionProxy('ldap');
 *   $res  = $ldap->connect($host, $port);
 *   $bind = $ldap->bind($res, $rdn, $pass);
 *
 * It removes the direct coupling to the extension's functions,
 * allowing dependency injection to replace the proxy object 
 * with a mock object for testing.
 *
 * @category   Mad
 * @package    Support
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Support_ExtensionProxy extends Mad_Support_Object
{
    /**
     * Name of the extension being proxied
     * @param string
     */
    protected $_extension;

    /**
     * Constructor
     * @param  string  $extension  Name of the extension to proxy
     */
    public function __construct($extension)
    {
        $this->attrReader('extension');

        if (! extension_loaded($extension)) {
            throw new Mad_Support_Exception("Required extension '$extension' is not loaded");
        }
        $this->_extension = $extension;
    }
    
    /**
     * Proxy method call back to the extension's procedural API
     */
    public function __call($method, $params)
    {
        return call_user_func_array("{$this->_extension}_{$method}", $params);
    }
}