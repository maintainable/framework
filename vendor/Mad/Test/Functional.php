<?php
/**
 * @category   Mad
 * @package    Mad_Test
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * Used for functional testing of controller classes
 *
 * @category   Mad
 * @package    Mad_Test
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
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
     * Evaluate the body of the response as XML to assert its contents.
     *
     *  - `id`           : the node with the given id attribute must match the corresponsing value.
     *  - `tag`          : the node type must match the corresponding value.
     *  - `attributes`   : a hash. The node's attributres must match the corresponsing values in the hash.
     *  - `content`      : The text content must match the given value.
     *  - `parent`       : a hash. The node's parent must match the corresponsing hash.
     *  - `child`        : a hash. At least one of the node's immediate children must meet the criteria described by the hash.
     *  - `ancestor`     : a hash. At least one of the node's ancestors must meet the criteria described by the hash.
     *  - `descendant`   : a hash. At least one of the node's descendants must meet the criteria described by the hash.
     *  - `children`     : a hash, for counting children of a node. Accepts the keys:
     *    - `count`        : a number which must equal the number of children that match
     *    - `less_than`    : the number of matching children must be greater than this number
     *    - `greater_than` : the number of matching children must be less than this number
     *    - `only`         : another hash consisting of the keys to use to match on the children, and only matching children will be counted
     *
     * {{code: php
     *     ...
     *     // assert there is an element with an id="my_id"
     *     $this->assertTag(array('id' => 'my_id'));
     *    
     *     // assert that there is a "span" tag
     *     $this->assertTag(array('tag' => 'span'));
     *    
     *     // assert that there is a "span" tag with the content "Hello World"
     *     $this->assertTag(array('tag'     => 'span',
     *                            'content' => 'Hello World'));
     *    
     *     // assert that there is a "span" tag with content matching the regexp pattern
     *     $this->assertTag(array('tag'     => 'span',
     *                            'content' => '/Hello D(erek|allas)/'));
     *    
     *     // assert that there is a "span" with an "list" class attribute
     *     $this->assertTag(array('tag' => 'span',
     *                            'attributes' => array('class' => 'list')));
     *    
     *     // assert that there is a "span" inside of a "div"
     *     $this->assertTag(array('tag'    => 'span',
     *                            'parent' => array('tag' => 'div')));
     *    
     *     // assert that there is a "span" somewhere inside a "table"
     *     $this->assertTag(array('tag'      => 'span',
     *                            'ascestor' => array('tag' => 'table')));
     *    
     *     // assert that there is a "span" with at least one "em" child
     *     $this->assertTag(array('tag'   => 'span',
     *                            'child' => array('tag' => 'em')));
     *    
     *     // assert that there is a "span" containing a (possibly nesxted) "strong" tag.
     *     $this->assertTag(array('tag'        => 'span',
     *                            'descendant' => array('tag' => 'strong')));
     *    
     *     // assert that there is a "span" containing 5-10 "em" tags as immediate children
     *     $this->assertTag(array('tag'       => 'span',
     *                            'children'  => array('less_than'    => 11,
     *                                                 'greater_than' => 4,
     *                                                 'only'         => array('tag' => 'em'))));
     *    
     *     // get funky: assert that there is a "div", with an "ul" ancestor and a "li" parent
     *     // (with class="enum"), and containing a "span" descendant that contains element with
     *     // id="my_test" and the text Hello World.. phew
     *     $this->assertTag(array('tag'      => 'div',
     *                            'ancestor' => array('tag' => 'ul'),
     *                            'parent'   => array('tag'        => 'li,
     *                                                'attributes' => array('class' => 'enum')),
     *                            'descendant' => array('tag'   => 'span',
     *                                                  'child' => array('id'      => 'my_test',
     *                                                                   'content' => 'Hello World'))));
     *     ...
     * }}
     *
     * @todo - add regex support for class names, and text content.
     * @param   array   $options
     * @param   string  $msg
     */
    public function assertTag($options, $msg=null)
    {
        $tags = $this->_findNodesInResponse($options);
        $this->assertTrue(count($tags) > 0 && $tags[0] instanceof DOMNode, $msg);
    }

    /**
     * The exact opposite of [[assertTag()]]. It ensures that the tag does not exist.
     *
     * {{code: php}
     *  <?php
     *  ...
     *  $this->assertNoTag(array('id' => 'explore_docs'));
     *  ...
     *  ?>
     * </code>
     *
     * @param   array   $options
     * @param   string  $msg
     */
    public function assertNoTag($options, $msg=null)
    {
        $tags = $this->_findNodesInResponse($options);
        $this->assertTrue($tags === false, $msg);
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
     *     // @todo - add support for this
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
     * @throws  Mad_Test_Exception
     */
    public function assertSelect($selector, $content=true, $exists=true, $msg=null)
    {
        $options = $this->_convertSelectToTag($selector, $content);
        $tags    = $this->_findNodesInResponse($options);

        // check if any elements exist with given content
        if (is_bool($content) || is_string($content)) {
            if ($content === false) {
                $this->assertTrue($tags === false, $msg);

            } else {
                if ($exists === true) {
                    $this->assertTrue(count($tags) > 0 && $tags[0] instanceof DOMNode, $msg);
                } elseif ($exists === false) {
                    $this->assertFalse(count($tags) > 0 && $tags[0] instanceof DOMNode, $msg);
                }
            }

        // check for specific number of elements
        } elseif (is_numeric($content)) {
            $tagCount = $tags ? count($tags) : 0;
            $this->assertEquals($content, $tagCount);

        // check for range number of elements
        } elseif (is_array($content) && (isset($content['>']) || isset($content['<']) || 
                                         isset($content['>=']) || isset($content['<=']))) {
            $tagCount = $tags ? count($tags) : 0;
            if (isset($content['>'])) {
                $this->assertTrue($tagCount > $content['>'], $msg);
            }
            if (isset($content['>='])) {
                $this->assertTrue($tagCount >= $content['>='], $msg);
            }
            if (isset($content['<'])) {
                $this->assertTrue($tagCount < $content['<'], $msg);
            }
            if (isset($content['<='])) {
                $this->assertTrue($tagCount <= $content['<='], $msg);
            }

        // invalid content option
        } else {
            throw new Mad_Test_Exception('Invalid options given for assertSelect()');
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
     * Convert an assertSelect statement to an assertTag struct. This will allow
     * us to make assertion statements using the simplified syntax, but still
     * use the underlying assertTag tests, and code to enforce the rules.
     *
     * @param   string  $selector
     * @param   mixed   $content
     * @return  array
     */
    protected function _convertSelectToTag($selector, $content=true)
    {
        $selector = trim(preg_replace("/\s+/", " ", $selector));

        // substitute spaces within a attribute value
        while (preg_match('/\[[^\]]+"[^"]+\s[^"]+"\]/', $selector)) {
            $selector = preg_replace('/(\[[^\]]+"[^"]+)\s([^"]+"\])/', "$1__SPACE__$2", $selector);
        }
        $elements = strstr($selector, ' ') ? explode(' ', $selector) : array($selector);

        $previousTag = array();
        foreach (array_reverse($elements) as $element) {
            $element = str_replace('__SPACE__', ' ', $element);

            // child selector
            if ($element == '>') {
                $previousTag = array('child' => $previousTag['descendant']);
                continue;
            }
            $tag = array();

            // match element tag
            preg_match("/^([^\.#\[]*)/", $element, $eltMatches);
            if (!empty($eltMatches[1])) {
                $tag['tag'] = $eltMatches[1];
            }

            // match attributes (\[[^\]]*\]*), ids (#[^\.#\[]*), and classes (\.[^\.#\[]*))
            preg_match_all("/(\[[^\]]*\]*|#[^\.#\[]*|\.[^\.#\[]*)/", $element, $matches);
            if (!empty($matches[1])) {
                $classes = array();
                $attrs   = array();
                foreach ($matches[1] as $match) {
                    // id matched
                    if (substr($match, 0, 1) == '#') {
                        $tag['id'] = substr($match, 1);

                    // class matched
                    } elseif (substr($match, 0, 1) == '.') {
                        $classes[] = substr($match, 1);

                    // attribute matched
                    } elseif (substr($match, 0, 1) == '[' && substr($match, -1, 1) == ']') {
                        $attribute = substr($match, 1, strlen($match) - 2);
                        $attribute = str_replace('"', '', $attribute);

                        // match single word
                        if (strstr($attribute, '~=')) {
                            list($key, $value) = explode('~=', $attribute);
                            $value = "/.*\b$value\b.*/";
                        // match substring
                        } elseif (strstr($attribute, '*=')) {
                            list($key, $value) = explode('*=', $attribute);
                            $value = "/.*$value.*/";
                        // exact match
                        } else {
                            list($key, $value) = explode('=', $attribute);
                        }
                        $attrs[$key] = $value;
                    }
                }
                if ($classes) $tag['class'] = join(' ', $classes);
                if ($attrs)   $tag['attributes'] = $attrs;
            }

            // tag content
            if (is_string($content)) $tag['content'] = $content;

            // determine previous child/descendants
            if (!empty($previousTag['descendant'])) {
                $tag['descendant'] = $previousTag['descendant'];
            } elseif (!empty($previousTag['child'])) {
                $tag['child'] = $previousTag['child'];
            }
            $previousTag = array('descendant' => $tag);
        }
        return $tag;
    }

    /**
     * Find nodes within the body response that match the given
     * options so that we can perform custom assertions on them
     *
     * @param   array   $options
     */
    protected function _findNodesInResponse($options)
    {
        $responseBody = $this->response->getBody();
        $dom = new DomDocument();
        $dom->preserveWhiteSpace = false;
        @$dom->loadHTML($responseBody);

        return $this->_findNodes($dom, $options);
    }

    /**
     * Parse out the options from the tag using DOM object tree (http://us2.php.net/dom)
     * @param   object  {@link DomDocument}
     * @param   array   $options
     * @return  array
     */
    protected function _findNodes($dom, $options)
    {
        $valid = array('id', 'class', 'tag', 'content', 'attributes', 'parent',
                       'child', 'ancestor', 'descendant', 'children');
        $options = Mad_Support_Base::assertValidKeys($options, $valid);
        $filtered = array();


        // find the element by id
        if ($options['id']) {
            $options['attributes']['id'] = $options['id'];
        }
        if ($options['class']) {
            $options['attributes']['class'] = $options['class'];
        }

        // find the element by a tag type
        if ($options['tag']) {
            $elements = $dom->getElementsByTagName($options['tag']);
            foreach ($elements as $element) {
                $nodes[] = $element;
            }
            if (empty($nodes)) return false;

        // no tag selected, get them all
        } else {
            $tags = array('a', 'abbr', 'acronym', 'address', 'area', 'b', 'base', 'bdo',
                          'big', 'blockquote', 'body', 'br', 'button', 'caption', 'cite',
                          'code', 'col', 'colgroup', 'dd', 'del', 'div', 'dfn', 'dl',
                          'dt', 'em', 'fieldset', 'form', 'frame', 'frameset', 'h1', 'h2',
                          'h3', 'h4', 'h5', 'h6', 'head', 'hr', 'html', 'i', 'iframe',
                          'img', 'input', 'ins', 'kbd', 'label', 'legend', 'li', 'link',
                          'map', 'meta', 'noframes', 'noscript', 'object', 'ol', 'optgroup',
                          'option', 'p', 'param', 'pre', 'q', 'samp', 'script', 'select',
                          'small', 'span', 'strong', 'style', 'sub', 'sup', 'table',
                          'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'title',
                          'tr', 'tt', 'ul', 'var');
            foreach ($tags as $tag) {
                $elements = $dom->getElementsByTagName($tag);
                foreach ($elements as $element) {
                    $nodes[] = $element;
                }
            }
            if (empty($nodes)) return false;
        }

        // filter by attributes
        if ($options['attributes']) {
            foreach ($nodes as $node) {
                $invalid = false;
                foreach ($options['attributes'] as $name=>$value) {

                    // match by regex
                    if (preg_match('#^/.*/.?$#', $value)) {
                        if (!preg_match($value, $node->getAttribute($name))) $invalid = true;

                    // class can match only a part
                    } elseif ($name == 'class') {
                        // split to individual classes
                        $findClasses = explode(' ', preg_replace("/\s+/", " ", $value));
                        $allClasses  = explode(' ', preg_replace("/\s+/", " ", $node->getAttribute($name)));
                        // make sure each class given is in the actual node
                        foreach ($findClasses as $findClass) {
                            if (!in_array($findClass, $allClasses)) $invalid = true;
                        }
                    } else {
                        // match by exact string
                        if ($node->getAttribute($name) != $value) $invalid = true;
                    }
                }
                // if every attribute given matched
                if (!$invalid) $filtered[] = $node;
            }
            $nodes = $filtered;
            $filtered = array();
            if (empty($nodes)) return false;
        }

        // filter by content
        if ($options['content'] !== null) {
            foreach ($nodes as $node) {
                $invalid = false;
                // match by regex
                if (preg_match('#^/.*/.?$#', $options['content'])) {
                    if (!preg_match($options['content'], $this->_getNodeText($node))) {
                        $invalid = true;
                    }

                // match by exact string
                } else {
                    if (strstr($this->_getNodeText($node), $options['content']) === false) {
                        $invalid = true;
                    }
                }
                if (!$invalid) $filtered[] = $node;
            }
            $nodes = $filtered;
            $filtered = array();
            if (empty($nodes)) return false;
        }

        // filter by parent node
        if ($options['parent']) {
            $parentNodes = $this->_findNodes($dom, $options['parent']);
            $parentNode = isset($parentNodes[0]) ? $parentNodes[0] : null;

            foreach ($nodes as $node) {
                if ($parentNode !== $node->parentNode) break;
                $filtered[] = $node;
            }
            $nodes = $filtered;
            $filtered = array();
            if (empty($nodes)) return false;
        }

        // filter by child node
        if ($options['child']) {
            $childNodes = $this->_findNodes($dom, $options['child']);
            $childNodes = !empty($childNodes) ? $childNodes : array();
            foreach ($nodes as $node) {
                foreach ($node->childNodes as $child) {
                    foreach ($childNodes as $childNode) {
                        if ($childNode === $child) $filtered[] = $node;
                    }
                }
            }
            $nodes = $filtered;
            $filtered = array();
            if (empty($nodes)) return false;
        }

        // filter by ancestor
        if ($options['ancestor']) {
            $ancestorNodes = $this->_findNodes($dom, $options['ancestor']);
            $ancestorNode = isset($ancestorNodes[0]) ? $ancestorNodes[0] : null;

            foreach ($nodes as $node) {
                $parent = $node->parentNode;
                while ($parent->nodeType != XML_HTML_DOCUMENT_NODE) {
                    if ($parent === $ancestorNode) $filtered[] = $node;
                    $parent = $parent->parentNode;
                }
            }
            $nodes = $filtered;
            $filtered = array();
            if (empty($nodes)) return false;
        }

        // filter by descendant
        if ($options['descendant']) {
            $descendantNodes = $this->_findNodes($dom, $options['descendant']);
            $descendantNodes = !empty($descendantNodes) ? $descendantNodes : array();

            foreach ($nodes as $node) {
                foreach ($this->_getDescendants($node) as $descendant) {
                    foreach ($descendantNodes as $descendantNode) {
                        if ($descendantNode === $descendant) $filtered[] = $node;
                    }
                }
            }
            $nodes = $filtered;
            $filtered = array();
            if (empty($nodes)) return false;
        }

        // filter by children
        if ($options['children']) {
            $validChild = array('count', 'greater_than', 'less_than', 'only');
            $childOptions = Mad_Support_Base::assertValidKeys($options['children'], $validChild);

            foreach ($nodes as $node) {
                $childNodes = $node->childNodes;
                foreach ($childNodes as $childNode) {
                    if ($childNode->nodeType !== XML_CDATA_SECTION_NODE &&
                        $childNode->nodeType !== XML_TEXT_NODE) {

                        $children[] = $childNode;
                    }
                }

                // we must have children to pass this filter
                if (!empty($children)) {

                   // exact count of children
                    if ($childOptions['count'] !== null) {
                        if (count($children) !== $childOptions['count']) break;

                    // range count of children
                    } elseif ($childOptions['less_than']    !== null &&
                              $childOptions['greater_than'] !== null) {
                        if (count($children) >= $childOptions['less_than'] ||
                            count($children) <= $childOptions['greater_than']) {
                            break;
                        }

                    // less than a given count
                    } elseif ($childOptions['less_than'] !== null) {
                        if (count($children) >= $childOptions['less_than']) break;

                    // more than a given count
                    } elseif ($childOptions['greater_than'] !== null) {
                        if (count($children) <= $childOptions['greater_than']) break;
                    }

                    // match each child against a specific tag
                    if ($childOptions['only']) {
                        $onlyNodes = $this->_findNodes($dom, $childOptions['only']);

                        // try to match each child to one of the 'only' nodes
                        foreach ($children as $child) {
                            $matched = false;
                            foreach ($onlyNodes as $onlyNode) {
                                if ($onlyNode === $child) $matched = true;
                            }
                            if (!$matched) break(2);
                        }
                    }
                    $filtered[] = $node;
                }
            }

            $nodes = $filtered;
            $filtered = array();
            if (empty($nodes)) return;
        }

        // return the first node that matches all criteria
        return !empty($nodes) ? $nodes : array();
    }

    /**
     * Get the text value of this node's child text node
     * @param   object  {@link DOMNode}
     */
    protected function _getNodeText($node)
    {
        $text = null;
        $childNodes = $node->childNodes instanceof DOMNodeList ? $node->childNodes : array();
        foreach ($childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $text .= trim($child->data).' ';
            } else {
                $text .= $this->_getNodeText($child);
            }
        }
        return str_replace('  ', ' ', $text);
    }

    /**
     * Recursively get flat array of all descendants of this node
     * @param   object  {@link DOMNode}
     */
    protected function _getDescendants($node)
    {
        $allChildren = array();
        $childNodes = $node->childNodes ? $node->childNodes : array();
        foreach ($childNodes as $child) {
            if ($child->nodeType === XML_CDATA_SECTION_NODE ||
                $child->nodeType === XML_TEXT_NODE) {
                continue;
            }
            $children = $this->_getDescendants($child);
            $allChildren = array_merge($allChildren, $children, array($child));
        }
        return isset($allChildren) ? $allChildren : array();
    }

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
        if ($method == 'POST' || $method == 'PUT') {
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
