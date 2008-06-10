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
 * @todo  Fully document each of the class properties.
 *
 * @category   Mad
 * @package    Mad_Controller
 * @subpackage Request
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Controller_Request_Http
{
    /**
     * Unique id per request.
     * @var string
     * @todo Assign default value.
     */
    protected $_requestId;

    /**
     * PHPSESSID
     * @var string
     */
    protected $_sessionId;

    // superglobal arrays
    protected $_get;
    protected $_post;
    protected $_files;
    protected $_server;
    protected $_env;
    protected $_request;

    // cookie/session info
    protected $_cookie;
    protected $_session;
    protected $_flash;

    protected $_method;
    protected $_remoteIp;
    protected $_port;
    protected $_https;
    protected $_isAjax;

    protected $_domain;
    protected $_uri;
    protected $_pathParams;

    /*##########################################################################
    # Construct/Destruct
    ##########################################################################*/

    /**
     * Request is populated with all the superglobals from page request if
     * data is not passed in.
     *
     * @param   array   $options  Associative array with all superglobals
     */
    public function __construct($options=array())
    {
        $this->_initRequestId();
        $this->_initSessionData();

        // superglobal data if not passed in thru constructor
        $this->_get     = isset($options['get'])     ? $options['get']     : $_GET;
        $this->_post    = isset($options['post'])    ? $options['post']    : $_POST;
        $this->_cookie  = isset($options['cookie'])  ? $options['cookie']  : $_COOKIE;
        $this->_request = isset($options['request']) ? $options['request'] : $_REQUEST;
        $this->_server  = isset($options['server'])  ? $options['server']  : $_SERVER;
        $this->_env     = isset($options['env'])     ? $options['env']     : $_ENV;
        $this->_pathParams = array();

        // use FileUpload object to store files
        $this->_setFilesSuperglobals();

        // disable all superglobal data to force us to use correct way
        $_GET = $_POST = $_FILES = $_COOKIE = $_REQUEST = $_SERVER = array();

        $this->_domain   = $this->getServer('SERVER_NAME');
        $this->_uri      = trim($this->getServer('REQUEST_URI'), '/');
        $this->_method   = $this->getServer('REQUEST_METHOD');
        $this->_remoteIp = $this->getServer('REMOTE_ADDR');
        $this->_port     = $this->getServer('SERVER_PORT');
        $this->_https    = $this->getServer('HTTPS');
        $this->_isAjax   = $this->getServer('HTTP_X_REQUESTED_WITH') == 'XMLHttpRequest';
    }


    /*##########################################################################
    # Public Methods
    ##########################################################################*/

    /**
     * Get list of all superglobals to pass into a different request
     *
     * @return  array
     */
    public function getGlobals()
    {
        return array('get'     => $this->_get,
                     'post'    => $this->_post,
                     'cookie'  => $this->_cookie,
                     'session' => $this->_session,
                     'files'   => $this->_files,
                     'request' => $this->_request,
                     'server'  => $this->_server,
                     'env'     => $this->_env);
    }

    /**
     * Get the domain for the current request
     * eg. https://www.maintainable.com/articles/show/123
     *     $domain is -> www.maintainable.com
     * 
     * @return  string
     */
    public function getDomain()
    {
        return $this->_domain;
    }

    /**
     * Get the host for the current request
     * eg. http://www.maintainable.com:3000/articles/show/123
     *     $host is -> http://www.maintainablesoftware.com:3000
     *
     * @param   boolean $usePort
     * @return  string
     */
    public function getHost($usePort=false)
    {
        $scheme = 'http'.($this->_https == 'on' ? 's' : null);
        $port   = $usePort && !empty($this->_port) && $this->_port != '80' ? ':'.$this->_port : null;
        return "{$scheme}://{$this->_domain}$port";
    }
    
    /**
     * @todo    add ssl support
     * @return  string
     */
    public function getProtocol()
    {
        return 'http://';
    }

    /**
     * Get the uri for the current request
     * eg. https://www.maintainable.com/articles/show/123?page=1
     *     $uri is -> articles/show/123?page=1
     * 
     * @return  string
     */
    public function getUri()
    {
        return $this->_uri;
    }

    /**
     * Get the path from the URI. (strip get params)
     * eg. https://www.maintainable.com/articles/show/123?page=1
     *     $path is -> articles/show/123
     * 
     * @return  string
     */
    public function getPath()
    {
        $path = $this->_uri;
        if (strstr($path, '?')) {
            $path = trim(substr($path, 0, strpos($path, '?')), '/');
        }
        return $path;
    }

    /**
     * Get the http request method:
     *  eg. GET, POST, PUT, DELETE
     *
     * @return  string
     */
    public function getMethod()
    {
        $methods = array('GET', 'HEAD', 'PUT', 'POST', 'DELETE', 'OPTIONS');
        
        if ($this->_method == 'POST') {
            if (isset($this->_post['_method'])) {
                $faked = strtoupper($this->_post['_method']);
                if (in_array($faked, $methods)) { return $faked; }
            }
        }
        
        return $this->_method;
    }

    /**
     * Get the remote Ip address as a dotted decimal string.
     * 
     * @return  string
     */
    public function getRemoteIp()
    {
        return $this->_remoteIp;
    }

    /**
     * Get server variable with the specified $name
     * 
     * @param   string  $name
     * @return  string
     */
    public function getServer($name)
    {
        return isset($this->_server[$name]) ? $this->_server[$name] : null;
    }

    /**
     * Get environment variable with the specified $name
     * 
     * @param   string  $name
     * @return  string
     */
    public function getEnv($name)
    {
        return isset($this->_env[$name]) ? $this->_env[$name] : null;
    }

    /**
     * Get cookie value from specified $name OR get All when $name isn't passed in
     * 
     * @param   string  $name
     * @param   string  $default
     * @return  string
     */
    public function getCookie($name=null, $default=null)
    {
        if (isset($name)) {
            return isset($this->_cookie[$name]) ? $this->_cookie[$name] : $default;
        } else {
            return $this->_cookie;
        }
    }

    /**
     * Get session value from session data by $name or ALL when $name isn't passed in
     * 
     * @param   string  $name
     * @param   string  $default
     * @return  mixed
     */
    public function getSession($name=null, $default=null)
    {
        if (isset($name)) {
            return isset($this->_session[$name]) ? $this->_session[$name] : $default;
        } else {
            return $this->_session;
        }
    }

    /**
     * Get flash value from session data by $name or ALL when $name isn't passed in
     * 
     * @param   string  $name
     * @return  mixed
     */
    public function getFlash($name=null, $default=null)
    {
        if (isset($name)) {
            return isset($this->_flash[$name]) ? $this->_flash[$name] : $default;
        } else {
            return $this->_flash;
        }
    }

    /**
     * Set flash data for the current request
     * 
     * @param   string  $name
     * @param   mixed   $value
     */
    public function setFlash($name, $value=null)
    {
       $this->_flash[$name] = $value; 
    }

    /**
     * Get a combination of all parameters. We have to do 
     * some wacky loops to make sure that nested values in one 
     * param list don't overwrite other nested values
     * 
     * @return  array
     */
    public function getAllParams()
    {
        $allParams = array();
        $paramArrays = array($this->_pathParams, $this->_get, $this->_post, $this->_files);
        foreach ($paramArrays as $params) {
            foreach ((array)$params as $key => $value) {
                if (!is_array($value) || !isset($allParams[$key])) {
                    $allParams[$key] = $value;
                } else {
                    $allParams[$key] = array_merge($allParams[$key], $value);
                }
            }
        }
        return $allParams;
    }

    /**
     * Get entire list of $_GET parameters
     * @return  array
     */
    public function getGetParams()
    {
        return $this->_get;
    }

    /**
     * Get entire list of $_POST parameters
     * 
     * @return  array
     */
    public function getPostParams()
    {
        return $this->_post;
    }

    /**
     * Get entire list of $_FILES parameters
     * 
     * @return  array
     */
    public function getFilesParams()
    {
        return $this->_files;
    }

    /**
     * Get entire list of parameters set by {@link Mad_Controller_Route_Path} for
     * the current request
     * 
     * @return  array
     */
    public function getPathParams()
    {
        return $this->_pathParams;
    }

    /**
     * Get the unique ID generated for this request
     * @see     _initRequestId()
     * @return  string
     */
    public function getRequestId()
    {
        return $this->_requestId;
    }

    /**
     * Get the session ID of this request (PHPSESSID)
     * @see    _initSession()
     * @return string
     */
    public function getSessionId()
    {
        return $this->_sessionId;
    }

    /*##########################################################################
    # Modifiers
    ##########################################################################*/

    /**
     * Set the uri and parse it for useful info
     * 
     * @param   string  $uri
     */
    public function setUri($uri)
    {
        $this->_uri = trim($uri, '/');
    }

    /**
     * When the {@link Mad_Controller_Dispatcher} determines the
     * correct {@link Mad_Controller_Route_Path} to match the url, it uses the
     * Routing object data to set appropriate variables so that they can be passed
     * to the Controller object.
     * 
     * @param   array   $params
     */
    public function setPathParams($params)
    {
        $this->_pathParams = !empty($params) ? $params : array();
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


    /*##########################################################################
    # Private Methods
    ##########################################################################*/

    /**
     * Uniquely identify each request from others. This aids in threading
     *  related log requests during troubleshooting on a busy server
     */
    private function _initRequestId()
    {
        $uuid = new OmniTI_Util_UUID();
        $this->_requestId = $uuid->toRFC4122String();
    }

    /**
     * Start up default session storage, and get stored data. 
     * 
     * @todo    further investigate session_cache_limiter() on ie6 (see below)
     * @todo    implement active record session store
     */
    protected function _initSessionData()
    {
        $this->_sessionId = session_id();
        
        if (! strlen($this->_sessionId)) {
            // internet explorer 6 will ignore the filename/content-type during
            // sendfile over ssl unless session_cache_limiter('public') is set 
            // http://joseph.randomnetworks.com/archives/2004/10/01/making-ie-accept-file-downloads/
            $agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
            if (strpos($agent, 'MSIE') !== false) {
                session_cache_limiter("public");
            }
            
            session_start();
            $this->_sessionId = session_id();
        }

        $this->_flash = isset($_SESSION['_flash']) ? $_SESSION['_flash'] : null;
        unset($_SESSION['_flash']);
        
        // Important: Setting "$this->_session = $_SESSION" does NOT work.
        $this->_session = array();
        if (is_array($_SESSION)) {
            foreach($_SESSION as $key => $value) {
                $this->_session[$key] = $value;
            }
        }
    }

    /**
     * Initialize the File upload information
     */
    protected function _setFilesSuperglobals()
    {
        if (empty($_FILES)) { 
            $this->_files = array(); 
            return; 
        }
        $_FILES = array_map(array($this, '_fixNestedFiles'), $_FILES);

        // create FileUpload object of of the file options
        foreach ((array)$_FILES as $name => $options) {
            if (isset($options['tmp_name'])) {
                $this->_files[$name] = new Mad_Controller_FileUpload($options);
            } else {
                foreach ($options as $attr => $data) {
                    $this->_files[$name][$attr] = new Mad_Controller_FileUpload($data);
                }
            }
        }
    }

    /**
     * fix $_FILES superglobal array. (PHP mungles data when we use brackets)
     * 
     * @link http://www.shauninman.com/archive/2006/11/30/fixing_the_files_superglobal
     * @param   array   $group
     */
    protected function _fixNestedFiles($group) 
    {
        // only rearrange nested files
        if (!is_array($group['tmp_name'])) { return $group; }

        foreach ($group as $property => $arr) {
            foreach ($arr as $item => $value) {
                $result[$item][$property] = $value;
            }
        }
        return $result;
    }
}
