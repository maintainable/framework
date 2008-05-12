<?php
/**
 * Base class for script files
 *
 * @category   Mad
 * @package    Mad_Script
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * Base class for script files
 *
 * @category   Mad
 * @package    Mad_Script
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Script_Base
{
    /**
     * Generic data struct for storing overloaded attributes
     * @var object
     */
    protected $_data   = array();


    /*##########################################################################
    # Construct
    ##########################################################################*/

    /**
     * Take the array of arguments given
     * @param   array   $args
     */
    public function __construct()
    {
    }


    /*##########################################################################
    # Magic Methods
    ##########################################################################*/

    /**
     * Only instantiate the connection if called
     * @param   string  $name
     */
    public function __get($name)
    {
        // only instantiate connection if used
        if ($name == '_connection') {
            if (!isset($this->_data['_connection'])) {
                $this->_data['_connection'] = $this->_connect();
            }
            return $this->_data['_connection'];
        }
    }


    /*##########################################################################
    # STDOUT
    ##########################################################################*/

    /**
     * Send data to <STDOUT>
     * @param   string  $msg
     */
    protected function _print($msg)
    {
        $msg = preg_replace("#^([^/]*).*?(app|config|test|db)/(.*)#", '$1/$2/$3', $msg);
        fwrite(STDOUT, "$msg\n");
    }

    /**
     * Get data from <STDIN>
     * @param   string  $msg
     * @return  string
     */
    protected function _prompt($msg)
    {
        fwrite(STDOUT, "$msg");
        return str_replace("\n", '', fgets(STDIN));
    }

    /**
     * Exit execution of the script and send a msg to the user
     * @param   string  $msg
     */
    protected function _exit($msg=null)
    {
        $this->_disconnect();
        fwrite(STDOUT, "$msg\n");
        exit(0);
    }


    /*##########################################################################
    # Database methods
    ##########################################################################*/

    /**
     * Connect to the database.
     * 
     * @return  object  {@link Mad_Model_ConnectionAdapter_Abstract}
     */
    protected function _connect()
    {
        if (! Mad_Model_Base::isConnected()) {
            Mad_Model_Base::establishConnection(MAD_ENV);
        }
        return Mad_Model_Base::connection();
    }

    /**
     * Disconnect from the database. 
     */
    protected function _disconnect()
    {
        if (Mad_Model_Base::isConnected()) {
            $this->_connection->disconnect();
        }
    }
}
