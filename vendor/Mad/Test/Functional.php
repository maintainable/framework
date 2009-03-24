<?php
/**
 * @category   Mad
 * @package    Mad_Test
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * Used for functional testing of controller classes
 *
 * @category   Mad
 * @package    Mad_Test
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
abstract class Mad_Test_Functional extends Mad_Test_Unit
{
    /**
     * The controller that was created from the routed request
     * @var Mad_Controller_Base
     */
    protected $controller;

    /**
     * If the route was recognized
     * @var boolean
     */
    protected $_recognized;

    /**
     * Cached DOMDocument parsed from response. @see assertSelect() 
     * @var null|DOMDocument
     */
    protected $_responseDom;

    /**
     * Test data available to sub-classes for testing requests
     */
    protected $expire  = '15 mins';
    protected $session = array();
    protected $cookies = array();
    protected $flash   = array();
    protected $get     = array();
    protected $post    = array();
    protected $files   = array();

    public function setUp()
    {
        $this->request  = new Mad_Controller_Request_Mock();
        $this->response = new Mad_Controller_Response_Mock();
    }
    
    /*##########################################################################
    # Methods available to Test subclasses
    ##########################################################################*/

    /**
     * Simulate a GET request to the controller
     *
     * {{code: php
     *  ...
     *  $this->get('/unit_test/test_action/123', array('myGetVar' => 1));
     *  $this->assertResponse('success');
     *  ...
     * }}
     *
     * @param   string  $url
     * @param   array   $options
     * @param   array   $session
     */
    public function get($url, $options=null, $session=null)
    {
        $this->_initParams('GET', $options, $session);
        $this->_sendRequest($url, 'GET');
    }

    /**
     * Simulate a POST request to the controller
     *
     * {{code: php
     *  ...
     *  $this->post('/unit_test/test_action/123', array('myPostVar' => 1));
     *  $this->assertResponse('success');
     *  ...
     * }}
     *
     * @param   string  $url
     * @param   array   $options
     * @param   array   $session
     */
    public function post($url, $options=null, $session=null)
    {
        $this->_initParams('POST', $options, $session);
        $this->_sendRequest($url, 'POST');
    }

    /**
     * Simulate a PUT request to the controller
     *
     * {{code: php
     *  ...
     *  $this->put('/unit_test/test_action/123', array('myGetVar' => 1));
     *  $this->assertResponse('success');
     *  ...
     * }}
     *
     * @param   string  $url
     * @param   array   $options
     * @param   array   $session
     */
    public function put($url, $options=null, $session=null)
    {
        $this->_initParams('PUT', $options, $session);
        $this->_sendRequest($url, 'PUT');
    }

    /**
     * Simulate a DELETE request to the controller
     *
     * {{code: php
     *  ...
     *  $this->delete('/unit_test/test_action/123', array('myGetVar' => 1));
     *  $this->assertResponse('success');
     *  ...
     * }}
     *
     * @param   string  $url
     * @param   array   $options
     * @param   array   $session
     */
    public function delete($url, $options=null, $session=null)
    {
        $this->_initParams('DELETE', $options, $session);
        $this->_sendRequest($url, 'DELETE');
    }

    /**
     * Simulate a xmlhttp post request to the controller
     *
     * {{code: php
     *  ...
     *  $this->xhr('/unit_test/test_action/123', array('myPostVar' => 1));
     *  $this->assertResponse('success');
     *  ...
     * }}
     *
     * @param   string  $url
     * @param   array   $options
     * @param   array   $session
     */
    public function xhr($url, $options=null, $session=null)
    {
        $this->request->setServer("HTTP_X_REQUESTED_WITH", "XMLHttpRequest");
        $this->request->setServer("HTTP_ACCEPT", "text/javascript, text/html, application/xml, text/xml, */*");

        $this->_initParams('POST', $options, $session);
        $this->_sendRequest($url, 'POST');
    }

    /**
     * Instantiate the controller using routes, but don't actually process the
     *  request. This will instantiate the $this->controller, but not $this->response
     *
     * {{code: php
     *  ...
     *  $this->recognize('/unit_test/test_action');
     *  $this->assertTrue($this->controller instanceof UnitTestController);
     *  ...
     * }}
     *
     * @param   string  $url
     */
    public function recognize($url)
    {
        $this->_initRouting();
        $this->_initRequest($url);
        $this->_initResponse();
        $this->_recognizeRoutes();
    }

    /**
     * Follow a redirect response that was set by a previous response
     *
     * {{code: php
     *  ...
     *  // initial request is redirected
     *  $this->get('/unit_test/test_action/123');
     *  $this->assertResponse('redirect');
     *
     *  // get() the page we redirected to
     *  $this->followRedirect();
     *  $this->assertResponse('success');
     *  ...
     * }}
     */
    public function followRedirect()
    {
        if ($redirectUrl = $this->response->getRedirectUrl()) {
            // data from previous request
            $this->session = $this->response->getSession();
            $this->cookies = $this->response->getCookie();
            $this->flash   = $this->response->getFlash();

            $this->response = new Mad_Controller_Response_Mock();

            $this->_initParams('GET', array(), $this->session);
            $this->_sendRequest($redirectUrl, 'GET');

        } else {
            $this->fail("Error attempting to follow a redirect for a page which did not redirect");
        }
    }

    /**
     * Get a cookie variable set during the controller action
     *
     * {{code: php
     *  ...
     *  $this->get('/unit_test/test_action/123', array('myGetVar' => 1));
     *  $this->assertEquals('Some cookie data', $this->getCookie('folderid'));
     *  ...
     * }}
     *
     * @param   string  $name
     * @param   string  $default
     * @return  string
     */
    protected function getCookie($name)
    {
        $cookie = $this->response->getCookie($name);
        return $cookie['value'];
    }

    /**
     * Get a session variable set during the controller action
     *
     * {{code: php
     *  <?php
     *  ...
     *  $this->get('/unit_test/test_action/123', array('myGetVar' => 1));
     *  $this->assertEquals('Some session data', $this->getSession('username'));
     *  ...
     * }}
     *
     * @param   string  $name
     * @return  string
     */
    protected function getSession($name)
    {
        return $this->response->getSession($name);
    }

    /**
     * Get a flash variable set during the controller action
     *
     * {{code: php
     *  ...
     *  $this->get('/unit_test/test_action/123', array('myGetVar' => 1));
     *  $this->assertEquals('Some flash message', $this->getFlash('notice'));
     *  ...
     * }}
     *
     * @param   string  $name
     * @return  string
     */
    protected function getFlash($name)
    {
        return $this->response->getFlash($name);
    }

    /**
     * Get the value that a template variable set
     *
     * {{code: php
     *  ...
     *  $this->get('/unit_test/test_action/123));
     *  $templateVarValue = $this->getAssigns('TEMPLATE_VAR_NAME');
     *  ...
     * }}
     *
     * @param   string  $name
     */
    protected function getAssigns($name)
    {
        return $this->controller->getAssigns($name);
    }


    /*##########################################################################
    # Assertion Methods
    ##########################################################################*/

    /**
     * Assert that the list of routing variables get set for the given URL
     *
     * {{code: php
     *  ...
     *  $this->assertRouting(array('id' => '123', 'ordering' => 'asc'));
     *  ...
     * }}
     *
     * @param   array   $params
     * @param   string  $msg
     */
    public function assertRouting($params, $msg=null)
    {
        $attrParams = $this->request->getPathParams();

        // unset params that we don't need to match
        unset($attrParams['controller']);
        unset($attrParams['action']);
        unset($attrParams[':controller']);
        unset($attrParams[':action']);

        $this->assertEquals($params, $attrParams, $msg);
    }

    /**
     * Assert that the action assigned the given template variable to the given value
     *
     * {{code: php
     *  ...
     *  $this->get('/unit_test/test_action/123');
     *  $this->assertAssigns('TEMPLATE_VAR_NAME', 'Template var value');
     *  ...
     * }}
     *
     * @param   string|array $name
     * @param   string       $value
     * @param   string       $msg
     */
    public function assertAssigns($name, $value=null, $msg=null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->assertEquals($value, $this->getAssigns($key), $msg);
            }
        } else {
            $this->assertEquals($value, $this->getAssigns($name), $msg);
        }
    }

    /**
     * Assert that the action assigned the given cookie variable to the given value
     *
     * {{code: php
     *  ...
     *  $this->get('/unit_test/test_action/123');
     *  $this->assertAssignsCookie('COOKIE_VAR_NAME', 'Cookie var value');
     *  ...
     * }}
     *
     * @param   string|array $name
     * @param   string       $value
     * @param   string       $msg
     */
    public function assertAssignsCookie($name, $value=null, $msg=null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->assertEquals($value, $this->getCookie($key), $msg);
            }
        } else {
            $this->assertEquals($value, $this->getCookie($name), $msg);
        }
    }

    /**
     * Assert that the action assigned the given session variable to the given value
     *
     * {{code: php
     *  ...
     *  $this->get('/unit_test/test_action/123');
     *  $this->assertAssignsSession('SESSION_VAR_NAME', 'Session var value');
     *  ...
     * }}
     *
     * @param   string|array $name
     * @param   string       $value
     * @param   string       $msg
     */
    public function assertAssignsSession($name, $value=null, $msg=null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->assertEquals($value, $this->getSession($key), $msg);
            }
        } else {
            $this->assertEquals($value, $this->getSession($name), $msg);
        }
    }

    /**
     * Assert that the action assigned the given flash variable to the given value.
     *
     * {{code: php
     *     ...
     *     $this->get('/unit_test/test_action/123');
     *     $this->assertAssignsFlash('FLASH_VAR', 'Flash var value');
     *     ...
     * }}
     *
     * @param   string|array $name
     * @param   string       $value
     * @param   string       $msg
     */
    public function assertAssignsFlash($name, $value=null, $msg=null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->assertEquals($value, $this->getFlash($key), $msg);
            }
        } else {
            $this->assertEquals($value, $this->getFlash($name), $msg);
        }
    }

    /**
     * Assert that the recognize didn't match any routes.
     *
     * {{code: php
     *  ...
     *  $this->assertNoRouting();
     *  ...
     * }}
     *
     * @param   string  $msg
     */
    public function assertNoRouting($msg=null)
    {
        $this->assertFalse($this->_recognized, $msg);
    }

    /**
     * Assert that the given action/controller was set during routing.
     *
     * {{code: php
     *     ...
     *     // assert that the list action was evaluated from the routes
     *     $this->assertAction('list');
     *
     *     // assert that getDocuments action and ExploreController were evaluated from routes
     *     $this->assertAction('getDocuments', 'ExploreController');
     *     ...
     * }}
     *
     * @param   string  $actionName
     * @param   string  $controllerName
     * @param   string  $msg
     */
    public function assertAction($actionName, $controllerName=null, $msg=null)
    {
        $attrParams = $this->request->getPathParams();

        // make sure action matches
        $this->assertEquals($actionName, $attrParams[':action']);

        // make sure controller matches
        if (isset($controllerName)) {
            $this->assertEquals($controllerName, $attrParams[':controller'], $msg);
        }
    }

    /**
     * Assert that a certain template was rendered.
     *
     * {{code: php
     *  ...
     *  $this->assertTemplate('explore/list');
     *  $this->assertTemplate('layouts/application');
     *  ...
     * }}
     *
     * @param   string  $templateName
     * @param   string  $msg
     */
    public function assertTemplate($templateName, $msg=null)
    {
        $templates = $this->controller->getTemplates();
        $msg = isset($msg) ? $msg : "$templateName is not in the list of templates used to render ".
                                    "this page: (".implode(', ', $templates).")";
        $this->assertContains(MAD_ROOT . $templateName, $templates, $msg);
    }

    /**
     * Assert that a certain response is given from the get/post request.
     *
     * {{code: php
     *  ...
     *  $this->assertResponse('success');  // status code is 200
     *  $this->assertResponse('redirect'); // status code is within 300..399
     *  $this->assertResponse('missing');  // status code is 404
     *  $this->assertResponse('error');    // status code is within 500..599
     *  $this->assertResponse(303);        // status code was 303
     *  ...
     * }}
     *
     * @param   mixed   $response
     * @param   string  $msg
     */
    public function assertResponse($status, $msg=null)
    {
        $statusCode  = $this->response->getStatusCode();
        $statusLevel = substr($statusCode, 0, 1);

        // check for success
        if ($status == 'success') {
            $msg = isset($msg) ? $msg : "expected response level: 200 (ok) ".
                                        "but was: $statusCode";
            $this->assertEquals(200, $statusCode, $msg);

        // check for redirect
        } elseif ($status == 'redirect') {
            $msg = isset($msg) ? $msg : "expected response level: 300..399 (redirect) ".
                                        "but was: $statusCode";
            $this->assertEquals('3', $statusLevel, $msg);

        // check for client error
        } elseif ($status == 'missing') {
            $msg = isset($msg) ? $msg : "expected response level: 404 (missing) ".
                                        "but was: $statusCode";
            $this->assertEquals(404, $statusCode, $msg);

        // check for server error
        } elseif ($status == 'error') {
            $msg = isset($msg) ? $msg : "expected response level: 500..599 (error) ".
                                        "but was: $statusCode";
            $this->assertEquals('5', $statusLevel, $msg);

        // compare numeric code
        } elseif (is_numeric($status)) {
            $msg = isset($msg) ? $msg : "expected response code: $status but was: $statusCode";
            $this->assertEquals($statusCode, $status, $msg);

        // invalid status
        } else {
            $this->fail("$status is not a valid status to assert. Try 'success', 'redirect', ".
                        "'missing', 'error', or a numeric code such as 200, 302, 404");
        }
    }

    /**
     * Assert that the request was redirected to a specified url.
     *
     * {{code: php
     *  ...
     *  // assert that the page will be redirected to "/explore/folder" for this request
     *  $this->assertRedirectedTo('/explore/folder');
     *  ...
     * }}
     *
     * @param   mixed   $response
     * @param   string  $msg
     */
    public function assertRedirectedTo($urlOrOptions, $msg=null)
    {
        $expected = $this->_urlWriter->urlFor($urlOrOptions);
        $actual   = $this->response->getRedirectUrl();
        
        $this->assertEquals($expected, $actual, $msg);
    }

    /**
     * Assert that the response text body containts the given string Snippit. Using
     * assertTag is much more powerful and the preferred method of checking for text.
     * However, certain tags don't seem to work with the DOM parser (like <script>).
     *
     * {{code: php
     *  ...
     *  $this->assertResponseContains('String expected to be in the html somewhere');
     *  ...
     *  $this->assertResponseContains('/^String/');
     *  ...
     * }}
     *
     * @param   string  $string
     * @param   string  $msg
     */
    public function assertResponseContains($string, $msg=null)
    {
        $responseBody = $this->response->getBody();
        if (preg_match('#^/.*/.?$#', $string)) {
            $this->assertTrue(preg_match($string, $responseBody) == 1, $msg);
        } else {
            $this->assertTrue(strpos($responseBody, $string) !== false, $msg);
        }
    }

    /**
     * Assert that the response text body DOES NOT containt the given string Snippit. Using
     * assertNoTag is much more powerful and the preferred method of checking for text.
     * However, certain tags don't seem to work with the DOM parser (like <script>).
     *
     * {{code: php
     *  ...
     *  $this->assertResponseDoesNotContain('String expected to be in the html somewhere');
     *  ...
     *  $this->assertResponseDoesNotContain('/^String/');
     *  ...
     * }}
     *
     * @param   string  $string
     * @param   string  $msg
     */
    public function assertResponseDoesNotContain($string, $msg=null)
    {
        $responseBody = $this->response->getBody();
        if (preg_match('#^/.*/.?$#', $string)) {
            $this->assertTrue(!preg_match($string, $responseBody) == 1, $msg);
        } else {
            $this->assertTrue(!strpos($responseBody, $string) !== false, $msg);
        }
    }

    /**
     * Make sure response content HTML passes DTD validation
     *  this doesn't seem to work with HTML 4.01 Trans.
     *
     * {{code: php
     *  ...
     *  $this->assertValid();
     *  ...
     *  ?>
     * }}
     *
     * @param   string  $msg
     */
    public function assertValid($msg=null)
    {
        $responseBody = $this->response->getBody();
        $dom = new DomDocument();
        $dom->loadHTML($responseBody);

        $this->assertTrue($dom->validate(), $msg);
    }


    /**
     * Test two XML strings for equivalency (e.g., identical up to reordering of attributes).
     */
    public function assertDomEquals($expected, $actual, $message = null)
    {
        $expectedDom = new DOMDocument();
        $expectedDom->loadXML($expected);

        $actualDom = new DOMDocument();
        $actualDom->loadXML($actual);

        $this->assertEquals($expectedDom->saveXML(), $actualDom->saveXML(), $message);
    }

    /**
     * CSS-style selector-based assertion that makes [[assertTag()]] look quite cumbersome.
     * The first argument is a string that is essentially a standard CSS selectors used to
     * match the element we want:
     *
     *  - `div`             : an element of type `div`
     *  - `div.class_nm`    : an element of type `div` whose class is `warning`
     *  - `div#myid`        : an element of type `div` whose ID equal to `myid`
     *  - `div[foo="bar"]`  : an element of type `div` whose `foo` attribute value is exactly
     *                        equal to `bar`
     *  - `div[foo~="bar"]` : an element of type `div` whose `foo` attribute value is a list
     *                        of space-separated values, one of which is exactly equal
     *                        to `bar`
     *  - `div[foo*="bar"]` : an element of type `div` whose `foo` attribute value contains
     *                        the substring `bar`
     *  - `div span`        : an span element descendant of a `div` element
     *  - `div > span`      : a span element which is a direct child of a `div` element
     *
     * We can also do combinations to any degree:
     *
     *  - `div#folder.open a[href="http://foo"][title="bar"].selected.big > span`
     *
     * The second argument determines what we're matching in the content or number of tags.
     * It can be one 4 options:
     *
     *  - `content`    : match the content of the tag
     *  - `true/false` : match if the tag exists/doesn't exist
     *  - `number`     : match a specific number of elements
     *  - `range`      : to match a range of elements, we can use an array with the options
     *                         `>` and `<`.
     *
     * {{code: php
     *     ...
     *     
     *     // There is an element with the id "binder_1" with the content "Test Foo"
     *     $this->assertSelect("#binder_1", "Test Foo");
     *     
     *     // There are 10 div elements with the class folder:
     *     $this->assertSelect("div.folder", 10);
     *     
     *     // There are more than 2, less than 10 li elements
     *     $this->assertSelect("ul > li", array('>' => 2, '<' => 10));
     *     
     *     // There are more than or exactly 2, less than or exactly 10 li elements
     *     $this->assertSelect("ul > li", array('>=' => 2, '<=' => 10));
     *     
     *     // The "#binder_foo" id exists
     *     $this->assertSelect('#binder_foo");
     *     $this->assertSelect('#binder_foo", true);
     *     
     *     // The "#binder_foo" id DOES NOT exist
     *     $this->assertSelect('#binder_foo", false);
     *     
     *     ...
     * }}
     *
     * @param   string  $selector
     * @param   mixed   $content
     * @param   boolean $exists
     * @param   string  $msg
     * @param   boolean $isHtml
     * @throws  Mad_Test_Exception
     */
    public function assertSelect($selector, $content=true, $exists=true, $msg=null, $isHtml=true)
    {
        if (! method_exists($this, 'assertSelectEquals')) {
            throw new Mad_Test_Exception('PHPUnit selector assertion support required');
        }

        // only parse response into dom once for better performance
        if ($this->_responseDom === null) {
            $body = $this->response->getBody();
            $this->_responseDom = PHPUnit_Util_XML::load($body, $isHtml);
        }

        if (is_string($content)) {
            if (preg_match('!^/.*/.?$!', $content)) {            
                $this->assertSelectRegexp($selector, $content, $exists, 
                                          $this->_responseDom, $msg, $isHtml);
            } else {
                $this->assertSelectEquals($selector, $content, $exists, 
                                          $this->_responseDom, $msg, $isHtml);
            }
        } else {
            $this->assertSelectCount($selector, $content, 
                                     $this->_responseDom, $msg, $isHtml);
        }
    }

    /**
     * Assert that the controller action sent a file. There are a few options you
     * can also assert
     *
     * {{code: php
     * ...
     * // assert that a document with the filname test.pdf was sent
     * $this->assertFileSent(array('filename' => 'test.pdf'));
     *
     * // assert that a document was setn as application/octet-stream type
     * $this->assertFileSent(array('type' => 'application/octet-stream'));
     *
     * // assert that a document was sent as an attachment
     * $this->assertFileSent(array('disposition' => 'attachment'));
     * ...
     * }}
     *
     * @param   array   $options
     * @param   string  $msg
     */
    public function assertFileSent($options=array(), $msg=null)
    {
        $valid = array('filename', 'type', 'disposition');
        $options = Mad_Support_Base::assertValidKeys($options, $valid);

        // parse headers for info in the format of:
        //   'Content-Type: application/octet-stream'
        //   'Content-Disposition: attachment; filename=test.jpg'
        foreach ($this->response->getHeaders() as $header => $value) {

            // parse out type
            if (strstr($header, 'Content-Type')) {
                preg_match('/Type: (.*)/', $header, $matches);
                $type = $matches[1];
            }

            // parse out disposition/filename
            if (strstr($header, 'Content-Disposition')) {
                preg_match('/Disposition: (.*); filename=(.*)/', $header, $matches);
                $disposition = $matches[1];
                $filename    = $matches[2];
            }
        }

        // make sure file was sent
        $msg = "File was not sent in this request";
        $this->assertTrue((!empty($disposition) && !empty($filename) && !empty($type)), $msg);

        // assert type
        if (!empty($options['type'])) {
            $this->assertEquals($options['type'], $type, 'Content-Type does not match expected');
        }

        // assert disposition
        if (!empty($options['disposition'])) {
            $this->assertEquals($options['disposition'], $disposition, 'Content-Disposition does not match expected');
        }

        // assert filename
        if (!empty($options['filename'])) {
            $this->assertEquals($options['filename'], $filename, 'filename does not match expected');
        }
    }


    /*##########################################################################
    # Protected methods
    ##########################################################################*/

    /**
     * Initiate the request to test a controller
     * @param   string  $url
     * @param   string  $requestMethod
     */
    protected function _sendRequest($uri, $requestMethod='GET')
    {
        $this->_initRouting();

        // if $uri doesn't start with '/', it's an action name
        // if it does start with '/', it's already the URI
        if (substr($uri, 0, 1) != '/') {
            $uri = $this->_urlWriter->urlFor(array('action' => $uri));
        }

        $this->_initRequest($uri, $requestMethod);
        $this->_initResponse();
        $this->_recognizeRoutes();

        // process the request if the url is dispatchable
        if ($this->_recognized) {
            $this->_processRequest();
        }
    }

    public function _initRouting()
    {
        // reload the routes
        $dispatcher = Mad_Controller_Dispatcher::getInstance();
        $dispatcher->reload();
        
        // Horde_Route_Utils required by UrlWriter
        $utils = $dispatcher->getRouteUtils();
        
        // build defaults for UrlWriter
        $defaults = array();
        $controller = $this->_getControllerNameFromTest();
        if ($controller) {
            $defaults['controller'] = $controller;
        }

        $this->_urlWriter = new Mad_Controller_UrlWriter($defaults);
    }

    /**
     * Divine the controller name from the functional test name,
     * since the controller or request may not be available at
     * the time the URL needs to be generated.
     */ 
    protected function _getControllerNameFromTest()
    {
        $class = get_class($this);
        if (preg_match('/^(.*)ControllerTest$/', $class, $matches) == 0) {
            return null;
        }
        return Mad_Support_Inflector::underscore($matches[1]);
    }

    /**
     * Initiate GET/POST params
     * @param   string  $method
     * @param   array   $options
     * @param   array   $session
     */
    protected function _initParams($method, $options, $session)
    {
        if ($method == 'POST' || $method == 'PUT' || $method == 'DELETE') {
            $this->post = isset($options) ? $options : array();
        } elseif ($method == 'GET') {
            $this->get  = isset($options) ? $options : array();
        }
        $this->session = isset($session) ? $session : array();
    }

    /**
     * Initialize the request/response object from the local test variables
     * @param   string  $url
     * @param   string  $requestMethod
     */
    protected function _initRequest($url, $requestMethod='GET')
    {
        // URL & request method
        $this->request->setUri($url);
        $this->request->setMethod($requestMethod);

        // set test data
        $this->request->setSession($this->session);
        $this->request->setCookie($this->cookies);
        $this->request->setFlash($this->flash);

        $this->request->setGet($this->get);
        $this->request->setPost($this->post);
        $this->request->setFiles($this->files);
    }

    /**
     * In standalone PHP, $_SESSION automatically carries any variables for
     * the lifetime of the session, even if they were not changed.  This is 
     * also true of MAD, which uses $_SESSION in Request_Http & Response_Http.
     *
     * However, Request_Mock & Response_Mock do not have this behavior.  The
     * response will not have the variables unless explicitly set on the response.
     *
     * This method copies the session array from the request into the response
     * before the action is run.  This ensures that if the controller does not
     * set a session variable, it will still be carried from the request into
     * the response.
     */
    protected function _initResponse()
    {
        $this->response->setSession( $this->request->getSession() );
    }

    /**
     * Get the controller & process the request
     */
    protected function _recognizeRoutes()
    {
        try {
            $this->controller = Mad_Controller_Dispatcher::getInstance()->recognize($this->request);
            $this->_recognized = true;
        } catch (Mad_Controller_Exception $e) {
            $this->_recognized = false;
        }
    }

    /**
     * Use the controller to process the request/response
     */
    protected function _processRequest()
    {
        $this->assertNotSame($this->_recognized, false, 'Request could not be processed because route was not recognized');
        $this->response = $this->controller->process($this->request, $this->response);
        $this->_responseDom = null;
        $this->_logRequest();
    }

    /**
     * Log the request method/uri/params
     * @todo temporarily disabled
     */
    protected function _logRequest()
    {
        return;
        $method = $this->request->getMethod();
        $uri    = $this->request->getUri();
        $params = array_merge($this->request->getGetParams(),
                              $this->request->getPostParams(),
                              $this->request->getPathParams());
        array_unique($params);
        unset($params[':controller']);
        unset($params['controller']);
        unset($params[':action']);
        unset($params['action']);

        $paramList = array();
        foreach ($params as $key=>$value) {
            $paramList[] = "$key => $value";
        }
        $paramStr = "[".implode(", ", $paramList)."]";

        Logger::pdebug("$method '$uri' \n                           $paramStr\n");
    }
}
