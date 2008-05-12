<?php
/**
 * @category   Mad
 * @package    Mad_Controller
 * @subpackage Request
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * Represents an HTTP request to the server. This class handles all headers/cookies/session
 * data so that it all has one point of entry for being written/retrieved.
 *
 * @category   Mad
 * @package    Mad_Controller
 * @subpackage Request
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Controller_Request_Mock extends Mad_Controller_Request_Http
{
    /*##########################################################################
    # Construct
    ##########################################################################*/

    /**
     * Request is populated with all the superglobals from page request.
     */
    public function __construct()
    {
        $this->initData();
        $this->setCookie(array());
        $this->setSession(array());
        $this->setFlash(array());
        $this->setSessionId('1');
        
        parent::__construct();
    }


    /*##########################################################################
    # Accessor methods to set data for testing purposes
    ##########################################################################*/

    /**
     * Set the session id
     *
     * @param  string  $id
     */
    public function setSessionId($id)
    {
        $this->_sessionId = $id;
    }

    /**
     * Set the cookies array.
     * 
     * @param   string  $name
     * @param   mixed   $value
     */
    public function setCookie($name, $value=null)
    {
        if (is_array($name)) {
            $this->_cookie = $name;
        } else {
            $this->_cookie[$name] = $value;
        }
    }

    /**
     * Set the session array.
     * 
     * @param   string  $name
     * @param   mixed   $value
     */
    public function setSession($name, $value=null)
    {
        if (is_array($name)) {
            $this->_session = $name;
        } else {
            $this->_session[$name] = $value;
        }
    }

    /**
     * Set the flash array.
     * 
     * @param   string  $name
     * @param   mixed   $value
     */
    public function setFlash($name, $value=null)
    {
        if (is_array($name)) {
            $this->_flash = $name;
        } else {
            $this->_flash[$name] = $value;
        }
    }

    /**
     * Set the request method type (GET|POST).
     *
     * @param   string  $requestMethod
     */
    public function setMethod($requestMethod)
    {
        $this->_method = $requestMethod;
    }

    /**
     * Set data for the get array.
     *
     * @param   array   $getData
     */
    public function setGet($getData)
    {
        $this->_get = $getData;
    }

    /**
     * Set data for the post array.
     *
     * @param   array   $postData
     */
    public function setPost($postData)
    {
        $this->_post = $postData;
    }

    /**
     * Set the data for the files array.
     *
     * @param   array   $filesData
     */
    public function setFiles($filesData)
    {
        $this->_files = $filesData;
    }

    /**
     * Set the remote IP address for this request.
     *
     * @param   string  $name
     * @param   string  $value
     */
    public function setServer($name, $value)
    {
        $this->_server[$name] = $value;
    }

    /**
     * Set the remote IP address for this request.
     *
     * @param   string  $remoteIp
     */
    public function setRemoteIp($remoteIp)
    {
        $this->_remoteIp = $remoteIp;
    }

    /**
     * Set the port used for this request.
     *
     * @param   int     $port
     */
    public function setPort($port)
    {
        $this->_port = $port;
    }

    /**
     * Start up default session storage, and get stored data. 
     * 
     * @todo    implement active record session store
     */
    protected function _initSessionData()
    {
        $this->_flash = isset($_SESSION['_flash']) ? $_SESSION['_flash'] : null;
        unset($_SESSION['_flash']);
        $this->_session = $_SESSION;
    }

    /*##########################################################################
    # Initialize http data for tests
    ##########################################################################*/

    /**
     * Populate vars to simulate a request on development.
     */
    public function initData()
    {
        $_SERVER = array(
            'DOCUMENT_ROOT'   => '/Users/derek/work/yarc',
            'HTTP_HOST'       => 'www.maintainable.com:33443',
            'HTTP_ACCEPT'     => 'text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5',
            'SERVER_PORT'     => '33443',
            'HTTPS'           => 'on',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; U; PPC Mac OS X Mach-O; en-US; rv:1.7.10) Gecko/20050716 Firefox/1.0.6',
            'PATH'            => '/usr/bin:/bin:/usr/sbin:/sbin:/Users/derek',
            'REMOTE_ADDR'     => '127.0.0.1',
            'SCRIPT_FILENAME' => '/Users/derek/work/yarc/public/dispatcher.php',
            'SERVER_ADDR'     => '127.0.0.1',
            'SERVER_NAME'     => 'www.maintainable.com',
            'REQUEST_METHOD'  => 'GET',
            'QUERY_STRING'    => 'test=true',
            'REQUEST_URI'     => '/hello/?test=true',
            'SCRIPT_NAME'     => '/dispatcher.php',
            'PATH_TRANSLATED' => '/Users/derek/work/yarc/public/dispatcher.php',
            'PHP_SELF'        => '/dispatcher.php'
        );

        $_ENV['TEST_DATA']    = 'my unit test data';

        $_COOKIE  = array('my_test_cookie'  => 'cookie value', 
                          'my_other_cookie' => 'cookie stuff');
        $_SESSION = array('my_test_session'  => 'session value',
                          'my_other_session' => 'session stuff',
                          '_flash' => array('my_test_flash'  => 'flash value', 
                                            'my_other_flash' => 'flash stuff'));

        $_GET     = array('document' => array('filesize' => '100'), 
                          'get_test1'  => 'true', 'get_test2' => 'go mets');
        $_POST    = array('document' => array('name' => 'hey'), 
                          'post_test1' => 'false', 'post_test2' => 'go yanks');
        $_REQUEST = array();

        // this wacky array represents how PHP mungles multi-dimensional file data
        $_FILES = array(
            'picture' => array('name'     => 'my_picture.gif', 
                              'type'     => 'image/gif', 
                              'size'     => '1234567', 
                              'tmp_name' => '/tmp/test1'), 
            'document' => array(
                'name'     => array('icon'  => 'dummy.gif', 
                                    'photo' => 'dummy.jpg'),
                'type'     => array('icon'  => 'image/gif', 
                                    'photo' => 'image/jpeg'),
                'tmp_name' => array('icon'  => '/tmp/test2', 
                                    'photo' => '/tmp/test3'),
                'size'     => array('icon'  => 32, 
                                    'photo' => 45))
        );
    }

}
