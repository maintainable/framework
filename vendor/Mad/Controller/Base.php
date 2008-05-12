<?php
/**
 * @category   Mad
 * @package    Mad_Controller
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * Base class for all Controller classes.
 *
 * @category   Mad
 * @package    Mad_Controller
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
abstract class Mad_Controller_Base
{
    /**
     * Generic data struct for storing overloaded attributes
     * @var array
     */
    private $_data = array();

    /**
     * Name of dir where views are for controller.
     * DocumentListController => DocumentList
     * @var string
     */
    private $_shortName = null;

    /**
     * The path to the view templates
     * @var string
     */
    private $_viewsDir = null;

    /**
     * If we are going to use a layout or not for the action
     * @var boolean
     */
    private $_useLayout = true;

    /**
     * Name of the layout template we're using
     * @var string
     */
    private $_layoutName = 'application';


    /*##########################################################################
    # Request/Response
    ##########################################################################*/

    /**
     * Params is the list of variables set through routes.
     * @var Mad_Support_ArrayObject
     */
    protected $params;

    /**
     * Proxy accessor to flash data in request and response objects.
     * @var Mad_Controller_Proxy_Flash
     */
    protected $flash;
    
    /**
     * Proxy accessor to session data in the request and response objects.
     * @var Mad_Controller_Proxy_Session
     */
    protected $session;

    /**
     * @var Mad_Controller_UrlWriter
     */
    protected $_urlWriter;

    /**
     * The request object we are processing
     * @var Mad_Controller_Request_Http
     * @todo Assign default value.
     */
    protected $_request;

    /**
     * The request object we are returning
     * @var Mad_Controller_Response_Http
     * @todo Assign default value.
     */
    protected $_response;

    /**
     * Have we performed a render on this controller
     * @var boolean
     */
    private $_performedRender = false;

    /**
     * Have we performed a redirect on this controller
     * @var boolean
     */
    private $_performedRedirect = false;

    /**
     * The current action being performed
     * @var string
     * @todo Assign default value.
     */
    private $_action;
    
    /**
     * Is there an action naming conflict with PHP?
     * @var boolean
     */
    private $_actionConflict = false;


    /*##########################################################################
    # Special method/actions
    ##########################################################################*/

    /**
     * Normal methods available as action requests.
     * @var array
     */
    private $_actionMethods = array();

    /**
     * Filters enable controllers to run shared pre and post processing code for its actions.
     * They are stored here by an associative array as follows:
     *  Methods that execute BEFORE the action: $_filters['before']
     *  Methods that execute AFTER the action:  $_filters['after']
     * @var array
     */
    private $_filters = array('before' => array(), 'after' => array());


    /*##########################################################################
    # Construct
    ##########################################################################*/

    /**
     * Construct new instance of the controller
     */
    public function __construct(){}

    /**
     * Only instantiate the template/logger objects if they're called. This allows
     * us to bypass instantiating the template object for a method that will only
     * ever redirect.
     *
     * @param   string  $name
     */
    public function __get($name)
    {
        // Only instantiate view if used
        if ($name == '_view') {
            if (!isset($this->_data['_view'])) {
                $this->_data['_view'] = new Mad_View_Base($this);
                $this->_data['_view']->addBuiltinHelpers();
            }
            return $this->_data['_view'];

        // Only instantiate logger if used
        } elseif ($name == '_logger') {
            if (!isset($this->_data['logger'])) {
                $this->_data['logger'] = $GLOBALS['MAD_DEFAULT_LOGGER'];
            }
            return $this->_data['logger'];
        }
    }

    /**
     * This gets called before action is performed in a controller. 
     * Override method in subclass to setup filters/helpers
     */ 
    protected function _initializeApplication() {}


    /*##########################################################################
    # Instance Methods
    ##########################################################################*/

    /**
     * Process the {@link Mad_Controller_Request_Http} and return
     * the {@link Mad_Controller_Response_Http}. This is the method that is called
     * for every request to be processed. It then determines which action to call
     * based on the parameters set within the {@link Mad_Controller_Request_Http}
     * object.
     *
     * <code>
     *  <?php
     *  ...
     *  $request  = new Mad_Controller_Request_Http();
     *  $response = new Mad_Controller_Response_Http();
     *
     *  $response = $controller->process($request, $response);
     *  ...
     *  ?>
     * </code>
     *
     * @param   Mad_Controller_Request_Http   $request
     * @param   Mad_Controller_Response_Http  $response
     * @return  Mad_Controller_Response_Http
     */
    public function process(Mad_Controller_Request_Http $request, Mad_Controller_Response_Http $response)
    {
        $this->_request   = $request;
        $this->_response  = $response;
        $this->_initParams();
        $this->_initProxies();

        $this->_viewsDir  = '/app/views/';
        $this->_shortName = str_replace('Controller', '', $this->params[':controller']);

        // templates
        $this->_initActionMethods();
        $this->_initViewPaths();
        $this->_initViewHelpers();
        $this->_initViewFilters();

        // Initialize application logic used thru all actions
        $this->_initializeApplication();
        if ($this->_performed()) return $this->_response;

        // Initialize sub-controller logic used thru all actions
        if (is_callable(array($this, '_initialize'))) {
            $this->_initialize();
        }

        // execute before filters, and return if we performed an action
        $this->_executeFilters('before');
        if ($this->_performed()) return $this->_response;

        // execute action & save any changes to sessionData
        $this->{$this->_action}();

        // execute after filters, and return if we performed an action
        $this->_executeFilters('after');
        if ($this->_performed()) return $this->_response;

        // render default if we haven't performed an action yet
        if (!$this->_performed()) {
            $this->render();
        }
        return $this->_response;
    }

    /**
     * Method to inspect the properties of the controller. Mosty useful for
     * unit testing assertions.
     *
     * @return  array
     */
    public function getAttributes()
    {
        $ref   = new ReflectionClass($this);
        $attrs = $ref->getProperties();
        foreach ($attrs as $attr) {
            $name = $attr->getName();
            $attrVals[$name] = $this->$name;
        }
        return $attrVals;
    }

    /**
     * Get an assigned template var. Mostly used for unit testing assertions.
     * 
     * @param   string  $name
     * @return  mixed
     */
    public function getAssigns($name)
    {
        return $this->_view->$name;
    }

    /**
     * Get the list of template used to construct the page. Most useful for
     * functional testing assertions.
     *
     * @return  array
     */
    public function getTemplates()
    {
        foreach ($this->_view->getPaths() as $dir) {
            $path = $dir.$this->_action.'.html';
            if (is_readable(MAD_ROOT.$path)) {
                $templates[] = $path;
            }
            $layout = $dir.$this->_layoutName.'.html';
            if ($this->usesLayout() && is_readable(MAD_ROOT.$layout)) {
                $templates[] = $layout;
            }
        }

        // funky sorting method to keep these in the same order
        return explode('**', implode('**', $templates));
    }

    /**
     * Get an instance of the view object
     * 
     * @return  Mad_View_Base
     */
    public function getView()
    {
        return $this->_view;
    }

    /**
     * Returns the current request object.
     *
     * @return Mad_Controller_Request_Http
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Returns the name of the current controller (e.g. "home" if HomeControler)
     *
     * @return string
     */
    public function getControllerName()
    {
        $params = $this->_request->getPathParams();
        return $params['controller'];
    }

    /** 
     * Returns the name of the current action (e.g. "index" if HomeController::index())
     * 
     * @return string
     */
    public function getActionName()
    {
        $params = $this->_request->getPathParams();
        return $params['action'];
    }

    /*##########################################################################
    # Methods available to Controllers Subclasses
    ##########################################################################*/

    /**
     * Render the response to the user. Actions are automatically rendered if no other
     * action is specified.
     *
     * <code>
     *  <?php
     *  ...
     *  $this->render(array('text'    => 'some text to render'));
     *  $this->render(array('action'  => 'actionName'));
     *  $this->render(array('nothing' => 1));
     *  ...
     *  ?>
     * </code>
     *
     * @see     renderText()
     * @see     renderAction()
     * @see     renderNothing()
     * @param   array  $options
     * @throws  Mad_Controller_Exception
     */
    protected function render($options=array())
    {
        // should not render/redirect more than once.
        if ($this->_performed()) {
            throw new Mad_Controller_Exception("Double render error: \"$this->_action\"");
        }
        
        // validate options
        $valid = array('text', 'nothing', 'action', 'status');
        $options = Mad_Support_Base::assertValidKeys($options, $valid);

        // stat response status
        if ($status = $options['status']) {
            $this->_response->setStatus($status);
        }

        // render text
        if ($text = $options['text']) {
            $this->renderText($text);

        // render template file
        } elseif ($action = $options['action']) {
            $this->renderAction($action);

        // render empty body
        } elseif ($options['nothing']) {
            $this->renderText('');

        // render defualt template
        } else {
            $this->renderAction($this->_action);
        }
    }

    /**
     * Render text directly to the screen without using a template
     *
     * <code>
     *  <?php
     *  ...
     *  $this->renderText('some text to render to the screen');
     *  ...
     *  ?>
     * </code>
     *
     * @param   string  $text
     */
    protected function renderText($text)
    {
        $this->_response->setBody($text);
        $this->_performedRender = true;
    }

    /**
     * The name of the action method will render by default.
     *
     * render 'listDocuments' template file
     * <code>
     *  <?php
     *  ...
     *  $this->renderAction('listDocuments');
     *  ...
     *  ?>
     * </code>
     *
     * @param   string  $name
     */
    protected function renderAction($name)
    {
        // current url
        $this->_view->currentUrl = $this->_request->getUri();

        // copy instance variables
        foreach (get_object_vars($this) as $key => $value) {
            $this->_view->$key = $value;
        }

        // add suffix
        if ($this->_actionConflict) {
            $name = str_replace('Action', '', $name);
        }
        if (! strstr($name, '.')) {
            $name .= '.html';
        }

        // prepend this controller's "short name" only if the action was
        // specified without a controller "short name".
        // e.g. index           -> Shortname/index
        //      Shortname/index -> Shortname/index
        if (! strstr($name, '/')) {
            $name = $this->_shortName.'/'.$name;
        }

        // respond to Javascript accept header
        if ($this->respondTo()->js) {
            // don't use application layout for JS
            if (strstr($this->_layoutName, 'application')) {
                $this->useLayout(false);
            }
            $this->_response->setHeader('Content-Type: text/javascript; charset=utf-8');
            $name = str_replace('.html', '.js', $name);

            if (! file_exists(MAD_ROOT.'/app/views/'.$name)) {
                throw new Mad_Controller_Exception("Missing template for: <b>$name</b>");
            }
        }
        if ($this->_useLayout) {
            $this->_view->contentForLayout = $this->_view->render($name);
            $text = $this->_view->render($this->_layoutName);
        } else {
            $text = $this->_view->render($name);
        }
        $this->renderText($text);
    }

    /**
     * Render blank content. This can be used anytime you want to send an 200 OK response
     * back to the user, but don't need to actually render any content.
     * This is mostly useful for AJAX requests.
     *
     * <code>
     *  <?php
     *  ...
     *  $this->renderNothing();
     *  ...
     *  ?>
     * </code>
     */
    protected function renderNothing()
    {
        $this->renderText('');
    }

    /**
     * Generate a URL
     * @see Mad_Controller_UrlWriter
     * 
     * @param  string|array  $first   named route, string, or options array
     * @param  array         $second  options array (if not in $first)   
     * @return string                 generated url    
     */
    protected function urlFor($first = array(), $second = array())
    {
        return $this->getUrlWriter()->urlFor($first, $second);
    }

    /**
     * Get an instance of UrlWriter for this controller.
     *
     * @return Mad_Controller_UrlWriter
     */
    public function getUrlWriter()
    {
        // instantiate UrlWriter that will generate URLs for this controller
        if (! $this->_urlWriter) {
            $defaults = array('controller' => $this->getControllerName());
            $this->_urlWriter = new Mad_Controller_UrlWriter($defaults);
        } 
        return $this->_urlWriter;
    }

    /**
     * Redirect to another page. Can redirect in a few different ways. URL redirects will
     * automagically determine the relative path based on which server we're on.
     * On development it will prepend /~{username}/{caseCode}/ to the URL.
     *
     * Redirect directly to an URL.
     * <code>
     *  <?php
     *  ...
     *  $this->redirectTo('/path/to/url');
     *  ...
     *  ?>
     * </code>
     *
     * Redirect to a specific controller/action
     * <code>
     *  <?php
     *  ...
     *  $this->redirectTo(
     *      array('controller' => 'browse',
     *            'action'     => 'briefcases',
     *            'id'         => '3',
     *            'sort'       => 'name'));
     *  ...
     *  ?>
     * </code>
     *
     * Redirect to a action within this same controller
     * <code>
     *  <?php
     *  ...
     *  $this->redirectTo(
     *      array('action'     => 'briefcases',
     *            'id'         => '3',
     *            'sort'       => 'name'));
     *  ...
     *  ?>
     * </code>
     *
     * @param  string|array  $first   named route, string, or options array
     * @param  array         $second  options array (if not in $first)   
     * @return null
     * @throws Mad_Controller_Exception
     */
    protected function redirectTo($first = array(), $second = array())
    {
        // should not render/redirect more than once
        if ($this->_performed()) {
            $msg = "Double render error: <b>$this->_action</b>";
            throw new Mad_Controller_Exception($msg);
        }

        if ($first === 'back') {
            // redirect to previous request
            $url = $this->_request->getServer('HTTP_REFERER');
            if (empty($url)) {
                $msg = "No HTTP_REFERER was set in the request to this action, ".
                       "so redirectTo('back') could not be called successfully. ".
                       " If this is a test, make sure to specify [\"HTTP_REFERER\"]";
                throw new Mad_Controller_Exception($msg);
            }
        } else {
            // generate the url
            $url = $this->getUrlWriter()->urlFor($first, $second);
        }

        $this->_response->setBody('Redirecting...');
        $this->_response->redirect($url);
        $this->_performedRedirect = true;
    }

    /**
     * Send a string containing binary data to the client. Typically the browser
     * will use a combination of the content type and the disposition, both set in
     * the options, to determine what to do with this data
     *
     * Options
     *  - filename: A suggestion to the browser of default filename to use when saving
     *  - type: The content type, defaulting to application/octet-stream
     *  - disposition: Suggest to the browser that the file should be displayed inline
     *    (option 'inline') or downloaded and saved (option 'attachemnt', the default)
     *
     * Additional headers can be set using $this->_response->setHeader()
     *
     * <code>
     *  <?php
     *  ...
     *  $data = $this->_generateReport();
     *  $this->sendData($data, array('filename'    => 'BriefcaseReport.csv',
     *                               'type'        => 'application/ms-excel',
     *                               'disposition' => 'attachment'));
     *  ...
     *  ?>
     * </code>
     *
     * @see     Mad_Controller_Response_Http::setHeader()
     * @param   string  $filepath
     * @param   array   $options
     */
    protected function sendData($data, $options=null)
    {
        $options['length'] = strlen($data);

        $this->_sendFileHeaders($options);
        $this->renderText($data);
    }

    /**
     * Send the contents of a file to the client.
     * Options
     *  - filename: A suggestion to the browser of default filename to use when saving
     *  - type: The content type, defaulting to application/octet-stream
     *  - disposition: Suggest to the browser that the file should be displayed inline
     *    (option 'inline') or downloaded and saved (option 'attachemnt', the default)
     *
     * The method sets these headers automatically:
     *  - Content-Length
     *  - Content-Type
     *  - Content-Transfer-Encoding
     *
     * Additional headers can be set using $this->_response->setHeader()
     *
     * <code>
     *  <?php
     *  ...
     *  // simple download
     *  $this->sendFile('/path/to.zip');
     *
     *  // Show a JPEG in a browser
     *  $this->sendFile('/path/to.jpeg', array('type'        => 'image/jpeg',
     *                                         'disposition' => 'inline'));
     *  ...
     *  ?>
     * </code>
     *
     * @see     Mad_Controller_Response_Http::setHeader()
     * @param   string  $filepath
     * @param   array   $options
     */
    protected function sendFile($filepath, $options=null)
    {
        // make sure the file exists
        if (!file_exists($filepath)) {
            throw new Mad_Controller_Exception("The file $filepath does not exist to send");
        }

        // default length/filename
        $options['length'] = filesize($filepath);
        if (!isset($options['filename'])) {
            $options['filename'] = basename($filepath);
        }

        $this->_sendFileHeaders($options);
        $this->renderText(file_get_contents($filepath));
    }
    
    /**
     * Very very hacked together simple implementation of http-accept headers
     * 
     * <code>
     *  $wants = $this->respondTo();
     *  if ($wants->html) { $this->redirectTo('back'); } 
     *  if ($wants->js)   { $this->render(array('nothing' => true)); }
     * </code>
     * 
     * @todo    a real implementation of mime-types
     * @return  object
     */
    protected function respondTo()
    {
        $accept = $this->_request->getServer('HTTP_ACCEPT');
        $uri    = $this->_request->getUri();
        
        $contentTypes = array('html' => false, 'js' => false);
        if (strstr($accept, 'text/javascript') || strstr($uri, '.js')) {
            $contentTypes['js'] = true;
        } elseif (strstr($accept, 'text/html') || strstr($uri, '.html')) {
            $contentTypes['html'] = true;
        }

        return (object)$contentTypes;
    }

    /*##########################################################################
    # Session Data Methods
    ##########################################################################*/


    /**
     * Check if this is a GET http request
     *
     * <code>
     *  <?php
     *  ...
     *  // only render form on GET requests
     *  if ($this->isGet()) {
     *      $this->render();
     *  }
     *  ...
     *  ?>
     * </code>
     *
     * @return  boolean
     */
    protected function isGet()
    {
        return $this->_request->getMethod() == 'GET';
    }

    /**
     * Check if this is a POST http request
     *
     * <code>
     *  <?php
     *  ...
     *  // form was submitted - update data
     *  if ($this->isPost()) {
     *      Folder::update($this->params('id'), $this->params('folder'));
     *
     *  // display form
     *  } else {
     *      $this->render();
     *  }
     *  ...
     *  ?>
     * </code>
     *
     * @return  boolean
     */
    protected function isPost()
    {
        return $this->_request->getMethod() == 'POST';
    }

    /*##########################################################################
    # Template Methods
    ##########################################################################*/

    /**
     * Specifiy if we want to use the layout for this controller. This allows us
     * to specifiy a layout for an entire controller and then selectively tell
     * certain actions to not use the layout.
     *
     * <code>
     *  <?php
     *  ...
     *  protected function _initialize()
     *  {
     *      $this->setLayout('application');
     *  }
     *
     *  // tell this method to not use the layout
     *  public function getMyData()
     *  {
     *      $this->useLayout(false);
     *  }
     *  ...
     *  ?>
     * </code>
     *
     * @param   boolean $useLayout
     */
    protected function useLayout($useLayout)
    {
        $this->_useLayout = $useLayout;
    }

    /**
     * Check if the action uses a layout.
     * @return  boolean
     */
    protected function usesLayout()
    {
        return $this->_useLayout;
    }

    /**
     * Set the layout template for the controller. Specify the name of the file in
     * the /app/views/layouts directory without the .html extension
     *
     * <code>
     *  <?php
     *  ...
     *  public function _initialize()
     *  {
     *      $this->setLayout('application');
     *  }
     *  ...
     *  ?>
     * </code>
     *
     * @param   string  $layoutName
     */
    protected function setLayout($layoutName)
    {
        $this->_useLayout  = true;
        $this->_layoutName = $layoutName;
    }

    /**
     * Add helper(s) for use in this controller
     * 
     * When the argument is a string, the method will provide the "Helper" 
     * suffix, require the file and include the module in the template class.  
     * 
     * <code>
     *  <?php
     *  ...
     *  $this->helper('Foo', 'Bar');
     *  ...
     *  ?>
     * </code>
     *
     * @param   string  $helperName
     */
    protected function helper($args)
    {
        foreach (func_get_args() as $helper) {
            $helperName = $helper.'Helper';
            $this->_view->addHelper(new $helperName($this->_view));
        }
    }


    /*##########################################################################
    # Filter Methods
    ##########################################################################*/

    /**
     * Perform these methods before the action is called. This is useful for
     * performing such operations as authentication which must be run before
     * every action.
     *
     * There are two options for filters.
     * - only: Only run the filter method before the given list of actions
     * - except: Run the filter methods before all actions except the given list
     *
     * <code>
     *  <?php
     *  ...
     *  function _initialize()
     *  {
     *      // Run loadSomeData() and doSomething() before every action
     *      $this->beforeFilter('loadSomeData', 'doSomething');
     *
     *      // Run loadCache() only before listDocs() action
     *      $this->beforeFilter('loadCache', array('only' => array(
     *                                             'DocumentController::listDocs')));
     *
     *      // Run authenticate() before all actions except login()
     *      $this->beforeFilter('authenticate', array('except' => array(
     *                                                'LoginController::login')));
     *  }
     *  ...
     *  ?>
     * </code>
     *
     * @param   string  $method
     * @param   array   $options
     */
    protected function beforeFilter($args)
    {
        $values = func_get_args();
        $last = end($values);
        $options = is_array($last) ? array_pop($values) : array();

        foreach ($values as $method) {
            $this->_addFilter('before', $method, $options);
        }
    }

    /**
     * Perform these methods after the action is called.  This is useful for
     * performing such operations as cleanup which must be run after every action.
     *
     * There are two options for filters.
     * - only: Only run the filter method after the given list of actions
     * - except: Run the filter methods after all actions except the given list
     *
     * <code>
     *  <?php
     *  ...
     *  function _initialize()
     *  {
     *      // Run saveCache() and cleanUp() after every action
     *      $this->afterFilter('saveCache', 'cleanUp');
     *
     *      // Run updateCache() only after insertDoc action
     *      $this->afterFilter('updateCache', array('only' => array(
     *                                              'DocumentController::insertDoc')));
     *
     *      // Run cleanSession() after all actions except index()
     *      $this->afterFilter('cleanSession', array('except' => array(
     *                                               'LoginController::index')));
     *  }
     *  ...
     *  ?>
     * </code>
     *
     * @param   string  $method
     * @param   array   $options
     */
    protected function afterFilter($args)
    {
        $values = func_get_args();
        $last = end($values);
        $options = is_array($last) ? array_pop($values) : array();

        foreach ($values as $method) {
            $this->_addFilter('after', $method, $options);
        }
    }
    
    /**
     * Skip the given before filter method(s) for the controller. 
     *
     * There are two options for skipping filters.
     * - only: Only skip the filter method before the given list of actions
     * - except: Skip the filter methods before all actions except the given list
     *
     * <code>
     *  <?php
     *  ...
     *  function _initialize()
     *  {
     *      // Skip running loadSomeData() before filter for the current controller
     *      $this->skipBeforeFilter('loadSomeData');
     *
     *      // Skip running loadCache() filter only before listDocs() action
     *      $this->skipBeforeFilter('loadCache', array('only' => array(
     *                                             'DocumentController::listDocs')));
     *
     *      // Skip running authenticate() filter before all actions except login()
     *      $this->skipBeforeFilter('authenticate', array('except' => array(
     *                                                    'LoginController::login')));
     *  }
     *  ...
     *  ?>
     * </code>
     *
     * @param   string  $method
     * @param   array   $options
     */
    protected function skipBeforeFilter($args)
    {
        $values = func_get_args();
        $last = end($values);
        $options = is_array($last) ? array_pop($values) : array();

        foreach ($values as $method) {
            $this->_removeFilter('before', $method, $options);
        }
    }

    /**
     * Skip the given after filter method(s) for the controller. 
     * 
     * There are two options for filters.
     * - only: Only skip the filter method after the given list of actions
     * - except: Skip the filter methods after all actions except the given list
     *
     * <code>
     *  <?php
     *  ...
     *  function _initialize()
     *  {
     *      // Skip running saveCache() after filter for this controller
     *      $this->skipAfterFilter('saveCache');
     *
     *      // Skip running updateCache() only after insertDoc action
     *      $this->skipAfterFilter('updateCache', array('only' => array(
     *                                                  'DocumentController::insertDoc')));
     *
     *      // Skip running cleanSession() after all actions except index()
     *      $this->skipAfterFilter('cleanSession', array('except' => array(
     *                                                   'LoginController::index')));
     *  }
     *  ...
     *  ?>
     * </code>
     *
     * @param   string  $method
     * @param   array   $options
     */
    protected function skipAfterFilter($args)
    {
        $values = func_get_args();
        $last = end($values);
        $options = is_array($last) ? array_pop($values) : array();

        foreach ($values as $method) {
            $this->_removeFilter('after', $method, $options);
        }
    }


    /*##########################################################################
    # Private Methods
    ##########################################################################*/

    /**
     * Each variable set through routing {@link Mad_Controller_Route_Path} is
     * availabie in controllers using the $params array.
     *
     * The controller also has access to GET/POST arrays using $params
     *
     * The action method to be performed is stored in $this->params[':action'] key
     */
    private function _initParams()
    {
        $hash = $this->_request->getAllParams();
        $this->params = new Mad_Support_ArrayObject($hash);

        $this->_action = $this->params->get(':action');
    }

    /**
     * Initialize proxy accessors.  Each proxy object connects the request and
     * and response object to the controller through an ArrayAccess interface
     * that allows convenient access to session, flash, and cookies.
     */
    private function _initProxies()
    {
        $this->session = new Mad_Controller_Proxy_Session($this->_request, $this->_response);
        $this->flash   = new Mad_Controller_Proxy_Flash($this->_request, $this->_response);
        $this->cookie  = new Mad_Controller_Proxy_Cookie($this->_request, $this->_response);
    }

    /**
     * Set the list of public actions that are available for this Controller.
     * Subclasses can remove methods from being publicly called by calling
     * {@link hideAction()}.
     *
     * @throws  Mad_Controller_Exception
     */
    private function _initActionMethods()
    {
        // Perform reflection to get the list of public methods
        $reflect = new ReflectionClass($this);
        $methods = $reflect->getMethods();
        foreach ($methods as $m) {
            if ($m->isPublic() && !$m->isConstructor() && !$m->isDestructor()  &&
                $m->getName() != 'process' && substr($m->getName(), 0, 1) != '_') {
                $this->_actionMethods[$m->getName()] = 1;
            }
        }

        // try action suffix. 
        if (!isset($this->_actionMethods[$this->_action]) && 
            isset($this->_actionMethods[$this->_action.'Action'])) {
            $this->_actionConflict = true;
            $this->_action = $this->_action.'Action';
        }
        // action isn't set, but there is a methodMissing() catchall method
        if (!isset($this->_actionMethods[$this->_action]) &&
            isset($this->_actionMethods['methodMissing'])) {
            $this->_action = 'methodMissing';

        // make sure we have an action set, and that there is no methodMissing() method
        } elseif (!isset($this->_actionMethods[$this->_action]) &&
                  !isset($this->_actionMethods['methodMissing'])) {
            $msg = 'Missing action: '.get_class($this)."::".$this->_action;
            throw new Mad_Controller_Exception($msg);
        }
    }

    /**
     * Initialize the view paths where the templates reside for this controller.
     * These are added in FIFO order, so if we do $this->renderAction('foo'),
     * in the BarController, the order it will search these directories will be:
     *  1. /views/Bar/foo.html
     *  2. /views/shared/foo.html
     *  3. /views/layouts/foo.html
     *  4. /views/foo.html (the default)
     *
     * We can specify a directory to look in instead of relying on the default order
     * by doing $this->renderAction('shared/foo').
     */
    private function _initViewPaths()
    {
        $this->_view->addPath($this->_viewsDir.'layouts');
        $this->_view->addPath($this->_viewsDir.'shared');
        $this->_view->addPath($this->_viewsDir.$this->_shortName);
    }

    /**
     * Initialize the default helpers for use in the views
     */
    private function _initViewHelpers()
    {
        $controllerHelper = $this->_shortName.'Helper';
        $this->_view->addHelper(new $controllerHelper($this->_view));
    }

    /**
     * Initialize the default filters for use in the views
     * @todo implement this once we've removed phplib support
     */
    private function _initViewFilters()
    {
        // $this->_view->addFilter(new TrimWhitespaceFilter);
        // $this->_view->addFilter(new GzipFilter);
    }

    /**
     * Send file headers for {@link sendFile()} and {@link sendData()}.
     *
     * @see     sendData()
     * @see     sendFile()
     * @param   array   $options
     */
    protected function _sendFileHeaders($options)
    {
        // validate options
        $valid = array('filename', 'type', 'disposition', 'length');
        $options = Mad_Support_Base::assertValidKeys($options, $valid);

        // default type/disposition/filename
        if (empty($options['type'])) {
            $options['type'] = 'application/octet-stream';
        }
        if (empty($options['disposition'])) {
            $options['disposition'] = 'attachment';
        }
        if (!empty($options['filename'])) {
            $options['disposition'] .= '; filename='.$options['filename'];
        }

        $this->_response->setHeader('Expires: 0');
        $this->_response->setHeader('Content-Length: '.$options['length']);
        $this->_response->setHeader('Content-Type: '.$options['type']);
        $this->_response->setHeader('Content-Disposition: '.$options['disposition']);
        $this->_response->setHeader('Content-Transfer-Encoding: binary');

        // allow for byte serving of pdf docs
        if (strstr($options['filename'], 'pdf')) {
	        $this->_response->setHeader("Accept-Ranges: bytes");
	        $this->_response->setHeader("bytes: 0-");
        }
    }

    /**
     * Add either an except/only filter
     * @param   string  $type
     * @param   string  $method
     * @param   array   $options
     * @throws  Mad_Controller_Exception
     */
    private function _addFilter($type, $method, $options)
    {
        // validate options
        $valid = array('except', 'only');
        $options = Mad_Support_Base::assertValidKeys($options, $valid);

        // make sure the method exists before adding as a filter
        if (!method_exists($this, $method)) {
            $msg = "The method \"$method\" can't be used as a filter because it does not exist";
            throw new Mad_Controller_Exception($msg);
        }

        // add to array of filters
        $this->_filters[$type][$method] = $options;
    }

    /**
     * Remove either an except/only filter
     * @param   string  $type
     * @param   string  $method
     * @param   array   $options
     */
    private function _removeFilter($type, $method, $options) 
    {
        // validate options
        $valid = array('except', 'only');
        $options = Mad_Support_Base::assertValidKeys($options, $valid);

        $thisAction = $this->params[':controller'] . '::' . $this->params[':action'];

        // only skip filter for specified controller::actions
        if ($options['only']) {
            $only = $this->_parseFilterController($options['only']);
            if (in_array($thisAction, $only)) {
                unset($this->_filters[$type][$method]);
            }

        // skip filter for all but the specified controller::actions
        } elseif ($options['except']) {
            $except = $this->_parseFilterController($options['except']);
            if (!in_array($thisAction, $except)) {
                unset($this->_filters[$type][$method]);
            }

        // skip for all methods in this controller
        } else {
            unset($this->_filters[$type][$method]);
        }
    }

    /**
     * Execute all filters to be run before the action.
     *
     * @todo    Make it so that we default to current controller for only/except options
     * @see     beforeFilter()
     * @see     afterFilter()
     * @param   string  $type
     * @return  boolean If we performed an action
     */
    private function _executeFilters($type)
    {
        // return if there are no filters
        if (!isset($this->_filters[$type])) return;

        // execute each filter. Kill the loop if an action redirects/renders
        foreach ($this->_filters[$type] as $methodName => $options) {
            $thisAction = $this->params[':controller'] . '::' . $this->params[':action'];

            // only execute filters for specific controller::actions
            if ($options['only']) {
                $options['only'] = $this->_parseFilterController($options['only']);
                if (!in_array($thisAction, $options['only'])) continue;

            // don't execute the filter for specific controller::actions
            } elseif ($options['except']) {
                $options['except'] = $this->_parseFilterController($options['except']);
                if (in_array($thisAction, $options['except'])) continue;
            }
            $this->$methodName();
            if ($type == 'before' && $this->_performed()) return true;
        }
        return $this->_performed();
    }

    /**
     * Each filter option should be an array of Controller::actions. If only
     * an action is given, set filter controller to the current controller
     *
     * @param   array   $actions
     * @return  array
     */
    protected function _parseFilterController($actions)
    {
        foreach ($actions as &$action) {
            if (!strstr($action, '::')) {
                $action = $this->params[':controller'].'::'.$action;
            }
        }
        return $actions;
    }

    /**
     * Check if a render or redirect has been performed
     * @return  boolean
     */
    protected function _performed()
    {
        return $this->_performedRender || $this->_performedRedirect;
    }
}
