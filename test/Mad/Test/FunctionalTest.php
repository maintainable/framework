<?php
/**
 * @category   Mad
 * @package    Mad_Test
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt 
 */

/**
 * Set environment
 */
if (!defined('MAD_ENV')) define('MAD_ENV', 'test');
if (!defined('MAD_ROOT')) {
    require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config/environment.php';
}

/**
 * @group      test
 * @category   Mad
 * @package    Mad_Test
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Test_FunctionalTest extends Mad_Test_Functional
{

    /*##########################################################################
    # Request methods
    ##########################################################################*/

    // test sending get request
    public function testGet()
    {
        $this->get('/unit_test/test_action/123');
        $this->assertTrue($this->response instanceof Mad_Controller_Response_Http);

        // html template
        $this->assertResponseContains("Rendered test action template");
    }

    // test sending post request
    public function testPost()
    {
        $this->post('/unit_test/test_action/123');
        $this->assertTrue($this->response instanceof Mad_Controller_Response_Http);

        // html template
        $this->assertResponseContains("Rendered test action template");
    }

    // test sending post request
    public function testXhr()
    {
        $this->xhr('/unit_test/test_action/123');
        $this->assertTrue($this->response instanceof Mad_Controller_Response_Http);

        // javascript template
        $this->assertResponseContains("\$('foo').show();");
    }

    // test getting cookie data
    public function testGetCookie()
    {
        $this->get('/unit_test/test_action/123');
        $this->assertEquals('test cookie data', $this->getCookie('functional_cookie'));
    }
   
    // test getting session data
    public function testGetSession()
    {
        $this->get('/unit_test/test_action/123');
        $this->assertEquals('test session data', $this->getSession('functional_session'));
    }
   
    // test getting flash data
    public function testGetFlash()
    {
        $this->get('/unit_test/test_action/123');
        $this->assertEquals('test flash data', $this->getFlash('functional_flash'));
    }
   
    public function testGetAssings()
    {
        $this->get('/unit_test/test_view/123');
        $this->assertEquals('test', $this->getAssigns('testVar'));
   
    }

    // test recognizing route
    public function testRecognize()
    {
        $this->recognize('/unit_test/test_action/123');
        $this->assertTrue($this->controller instanceof UnitTestController);
    }
   
    // test following a redirect
    public function testFollowRedirect()
    {
        $this->get('/unit_test/test_redirect_action/123');
        $this->assertResponse('redirect');
   
        $this->followRedirect();
        $this->assertResponse('success');
    }
   
    // test initializing the get
    public function testInitRequestGet()
    {
        $url = '/unit_test/test_action/123';
        $this->get = array('get1' => 'get test1');
   
        $this->_initRequest($url, 'GET');
        $this->assertEquals($this->get, $this->request->getGetParams());
    }
   
    // test initializing the post
    public function testInitRequestPost()
    {
        $url = '/unit_test/test_action/123';
        $this->post = array('post1' => 'post test1');
   
        $this->_initRequest($url, 'POST');
        $this->assertEquals($this->post, $this->request->getPostParams());
    }
   
    // test initializing the session
    public function testInitRequestSession()
    {
        $url = '/unit_test/test_action/123';
        $this->session = array('session'  => 'session test1');
        $this->flash   = array('flash1'   => 'flash test1');
   
        $this->_initRequest($url, 'GET');
        $this->assertEquals($this->session, $this->request->getSession());
        $this->assertEquals($this->flash, $this->request->getFlash());
    }
   
    // test initializing the routes
    public function testRecognizeRoutes()
    {
        $url = '/unit_test/test_action/123';
        $this->_initRequest($url, 'GET');
   
        $this->_recognizeRoutes();
        $this->assertTrue($this->controller instanceof UnitTestController);
    }
   
    // test initializing the routes
    public function testRecognizeNoRoutes()
    {
        $url = 'this-is-an-invalid-route';
        $this->_initRequest($url, 'GET');
        $this->_recognizeRoutes();

        $this->assertNoRouting();
    }
   
    /*##########################################################################
    # Assertion methods
    ##########################################################################*/

    // test assering the routing params
    public function testAssertRoutingTrue()
    {
        $url = '/unit_test/test_action/123';
        $this->recognize($url);
   
        $this->assertRouting(array('id' => '123'));
    }
   
    // test assering the routing params
    public function testAssertRoutingFalse()
    {
        $url = '/unit_test/test_action/123';
        $this->recognize($url);
   
        try {
            $this->assertRouting(array('test' => '123'));
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // test asserting a template variable was set
    public function testAssertAssignsTrue()
    {
        $this->get('/unit_test/test_action/123');
        $this->assertAssigns('testVariable', 'buga buga');
   
        $this->assertAssigns(array('testVariable' => 'buga buga'));
    }
   
    // test asserting a template variable wasn't set
     public function testAssertAssignsFalse()
     {
        $this->get('/unit_test/test_action/123');

        $e = null;
        try {
         $this->assertAssigns('testVariable', 'wrong value');
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);

        $e = null;
        try {
         $this->assertAssigns('testWrongVar', 'buga buga');
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
     }
   
    // test asserting cookie was set
    public function testAssertAssignsCookieTrue()
    {
        $this->get('/unit_test/test_action/123');
        $this->assertAssignsCookie('functional_cookie', 'test cookie data');
        $this->assertAssignsCookie(array('functional_cookie' => 'test cookie data'));
    }
   
    // test cookie wasn't set
    public function testAssertAssignsCookieFalse()
    {
        $this->get('/unit_test/test_action/123');
   
        $e = null;
        try {
            $this->assertAssignsCookie('functional_cookie', 'wrong value');
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
   
        $e = null;
        try {
            $this->assertAssignsCookie('test_wrong_cookie', 'test cookie data');
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
   
    // test asserting session was set
    public function testAssertAssignsSessionTrue()
    {
        $this->get('/unit_test/test_action/123');
        $this->assertAssignsSession('functional_session', 'test session data');
   
        $this->assertAssignsSession(array('functional_session' => 'test session data'));
    }
   
    // test session wasn't set
    public function testAssertAssignsSessionFalse()
    {
        $this->get('/unit_test/test_action/123');
   
        $e = null;
        try {
            $this->assertAssignsSession('functional_session', 'wrong value');
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
   
        $e = null;
        try {
            $this->assertAssignsSession('test_wrong_session', 'test session data');
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // test asserting flash was set
    public function testAssertAssignsFlashTrue()
    {
        $this->get('/unit_test/test_action/123');
        $this->assertAssignsFlash('functional_flash', 'test flash data');
   
        $this->assertAssignsFlash(array('functional_flash' => 'test flash data'));
    }
   
    // test flash wasn't set
    public function testAssertAssignsFlashFalse()
    {
        $this->get('/unit_test/test_action/123');
   
        $e = null;
        try {
            $this->assertAssignsFlash('functional_flash', 'wrong value');
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
   
        $e = null;
        try {
            $this->assertAssignsFlash('test_wrong_flash', 'test flash data');
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // test assering the routing params
    public function testAssertActionTrue()
    {
        $url = '/unit_test/test_action/123';
        $this->recognize($url);
   
        $this->assertAction('testAction');
    }
   
    // test assering the routing params
    public function testAssertActionFalse()
    {
        $url = '/unit_test/test_action/123';
        $this->recognize($url);
   
        try {
            $this->assertAction('testCrap');
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // test assering the routing params
    public function testAssertControllerTrue()
    {
        $url = '/unit_test/test_action/123';
        $this->recognize($url);
   
        $this->assertAction('testAction', 'UnitTestController');
    }

    // test assering the routing params
    public function testAssertControllerFalse()
    {
        $url = '/unit_test/test_action/123';
        $this->recognize($url);
   
        try {
            $this->assertAction('testAction', 'SomeOtherController');
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // test assertion of template usage
    public function testAssertTemplateTrue()
    {
        $url = '/unit_test/test_action/123';
        $this->get($url);
   
        $this->assertTemplate('/app/views/UnitTest/testAction.html');
    }
   
    // test assertion of template usage
    public function testAssertTemplateFalse()
    {
        $url = '/unit_test/test_action/123';
        $this->get($url);
   
        $e = null;
        try {
            $this->assertTemplate('SomeOther/testAction');
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // test assertion of redirect
    public function testAssertRedirectedPass()
    {
        $url = '/unit_test/test_redirect_action/123';
        $this->get($url);
        $this->assertRedirectedTo("/unit_test/test_action/123");
    }
   
    // test assertion of redirect
    public function testAssertRedirectedFail()
    {
        $url = '/unit_test/test_redirect_action/123';
        $this->get($url);
   
        $e = null;
        try {
            $this->assertRedirectedTo("/unit_test/some_bad_action/123");
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
   
    // test response body against string fragment
    public function testAssertStringResponseContainsPass()
    {
        $url = '/unit_test/test_action/123';
        $this->get($url);
        $this->assertResponseContains('Rendered test action template');
    }
   
    // test response body against string fragment
    public function testAssertStringResponseContainsFail()
    {
        $url = '/unit_test/test_action/123';
        $this->get($url);
   
        $e = null;
        try {
            $this->assertResponseContains('nada');
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // test response body against regex pattern
    public function testAssertRegexpResponseContainsPass()
    {
        $url = '/unit_test/test_action/123';
        $this->get($url);
        $this->assertResponseContains('/(a)ction template/');
    }
   
    // test response body against regex pattern
    public function testAssertRegexpModifierResponseContainsPass()
    {
        $url = '/unit_test/test_action/123';
        $this->get($url);
        $this->assertResponseContains('/(A)CTION TEMPLATE/i');
    }
   
    // test response body against regex pattern
    public function testAssertRegexpResponseContainsFail()
    {
        $url = '/unit_test/test_action/123';
        $this->get($url);
   
        $e = null;
        try {
            $this->assertResponseContains('/nada/');
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // test response body against string fragment
    public function testAssertStringResponseDoesNotContainPass()
    {
        $url = '/unit_test/test_action/123';
        $this->get($url);
        $this->assertResponseDoesNotContain('nada');
    }
   
    // test response body against string fragment
    public function testAssertStringResponseDoesNotContainFail()
    {
        $url = '/unit_test/test_action/123';
        $this->get($url);
   
        $e = null;
        try {
            $this->assertResponseDoesNotContain('Rendered test action template');
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // test response body against regex pattern
    public function testAssertRegexpResponseDoesNotContainPass()
    {
        $url = '/unit_test/test_action/123';
        $this->get($url);
        $this->assertResponseDoesNotContain('/nada/');
    }
   
    // test response body against regex pattern
    public function testAssertRegexpResponseDoesNotContainFail()
    {
        $url = '/unit_test/test_action/123';
        $this->get($url);
   
        $e = null;
        try {
            $this->assertResponseDoesNotContain('/(a)ction template/');
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
   
    /*##########################################################################
    # Assert response codes
    ##########################################################################*/

    // test assertion of response status
    public function testAssertResponseCodePass()
    {
        $url = '/unit_test/test_action/123';
        $this->get($url);
   
        $this->assertResponse(200);
    }
   
    // test assertion of response status
    public function testAssertResponseCodeFail()
    {
        $url = '/unit_test/test_action/123';
        $this->get($url);
   
        $e = null;
        try {
            $this->assertResponse(300);
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // test assertion of response status
    public function testAssertResponseStatusSuccessPass()
    {
        $url = '/unit_test/test_action/123';
        $this->get($url);
   
        $this->assertResponse('success');
    }
   
    // test assertion of response status
    public function testAssertResponseStatusSuccessFail()
    {
        $url = '/unit_test/test_redirect_action/123';
        $this->get($url);
   
        $e = null;
        try {
            $this->assertResponse('success');
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
   
    // test assertion of response status
    public function testAssertResponseStatusRedirectPass()
    {
        $url = '/unit_test/test_redirect_action/123';
        $this->get($url);
   
        $this->assertResponse('redirect');
    }
   
    // test assertion of response status
    public function testAssertResponseStatusRedirectFail()
    {
        $url = '/unit_test/test_action/123';
        $this->get($url);
   
        $e = null;
        try {
            $this->assertResponse('redirect');
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
   
    // test assertion of response status
    public function testAssertResponseStatusMissingPass()
    {
        $url = '/asdf/qwer';
        $this->get($url);

        $this->assertFalse($this->_recognized);
        $this->response->pageNotFound();

        $this->assertResponse('missing');
    }
   
    // test assertion of response status
    public function testAssertResponseStatusMissingFail()
    {
        $url = '/unit_test/test_action/123';
        $this->get($url);
        if (!$this->_recognized) {
            $this->response->pageNotFound();
        }
   
        $e = null;
        try {
            $this->assertResponse('missing');
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
   
    // test assertion of response status
    public function testAssertResponseStatusErrorPass()
    {
        $url = '/unit_test/test_action/123';
        $this->get($url);
        $this->response->setStatus('502 Bad Gateway');
   
        $this->assertResponse('error');
    }
   
    // test assertion of response status
    public function testAssertResponseStatusErrorFail()
    {
        $url = '/unit_test/test_action/123';
        $this->get($url);
   
        $e = null;
        try {
            $this->assertResponse('error');
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
   
    /*##########################################################################
    # Assert Tags
    ##########################################################################*/

    // match a node's type
    public function testAssertTagTypeTrue()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('tag' => 'html'));
    }
   
    // match a node's type
    public function testAssertTagTypeFalse()
    {
        $this->get('/unit_test/test_assert_tag');
        $e = null;
        try {
            $this->assertTag(array('tag' => 'code'));
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // match a text node by id
    public function testAssertTagIdTrue()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('id' => 'test_text'));
    }
   
    // match a text node by id
    public function testAssertTagIdFalse()
    {
        $this->get('/unit_test/test_assert_tag');
        $e = null;
        try {
            $this->assertTag(array('id' => 'test_text_doesnt_exist'));
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // match a text node's content by string
    public function testAssertTagStringContentTrue()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('id' => 'test_text', 'content' => 'My test tag content'));
    }
   
    // match a text node's content
    public function testAssertTagStringContentFalse()
    {
        $this->get('/unit_test/test_assert_tag');
        $e = null;
        try {
            $this->assertTag(array('id' => 'test_text', 'content' => 'My non existent tag content'));
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // match a text node's content by regexp
    public function testAssertTagRegexpContentTrue()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('id' => 'test_text', 'content' => '/test tag/'));
    }
   
    // match a text node's content by regexp
    public function testAssertTagRegexpModifierContentTrue()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('id' => 'test_text', 'content' => '/TEST TAG/i'));
    }
   
    // match a text node's content
    public function testAssertTagRegexpContentFalse()
    {
        $this->get('/unit_test/test_assert_tag');
        $e = null;
        try {
            $this->assertTag(array('id' => 'test_text', 'content' => '/asdf/'));
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // match a node's attributes
    public function testAssertTagAttributesTrueA()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('tag'        => 'span',
                               'attributes' => array('class' => 'test_class')));
    }
   
    // check we can have child/content/attributes all set
    public function testAssertTagAttributesTrueB()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('tag'        => 'div',
                               'attributes' => array('id' => 'test_child_id')));
    }
   
    // match a node's attributes
    public function testAssertTagAttributesFalse()
    {
        $this->get('/unit_test/test_assert_tag');
        $e = null;
        try {
            $this->assertTag(array('tag'        => 'span',
                                   'attributes' => array('class' => 'test_missing_class')));
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // match a node's attributes with regexp
    public function testAssertTagAttributesRegexpTrueA()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('tag'        => 'span',
                               'attributes' => array('class' => '/.+_class/')));
    }
   
    // match a node's attributes with regexp
    public function testAssertTagAttributesRegexpTrueB()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('tag'        => 'div',
                               'attributes' => array('id' => '/.+_child_.+/')));
    }
   
    public function testAssertTagAttributesRegexpModifierTrue()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('tag'        => 'div',
                               'attributes' => array('id' => '/.+_CHILD_.+/i')));
    }
   
    public function testAssertTagAttributesRegexpModifierFalse()
    {
        $this->get('/unit_test/test_assert_tag');
        $e = null;
        try {
            $this->assertTag(array('tag'        => 'div',
                                   'attributes' => array('id' => '/.+_CHILD_.+/')));
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // match a node's attributes with regexp
    public function testAssertTagAttributesRegexpFalse()
    {
        $this->get('/unit_test/test_assert_tag');
        $e = null;
        try {
            $this->assertTag(array('tag'        => 'span',
                                   'attributes' => array('class' => '/.+_missing_.+/')));
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // matching a class will match a substring (single class definition)
    public function testAssertTagAttributesMultiPartClassTrueA()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('tag' => 'div',
                               'id'  => 'test_multi_class',
                               'attributes' => array('class' => 'multi class')));
    }
   
    // matching a class will match a substring (single class definition)
    public function testAssertTagAttributesMultiPartClassTrueB()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('tag' => 'div',
                               'id'  => 'test_multi_class',
                               'attributes' => array('class' => 'multi')));
    }
   
    // matching a class will match a substring (single class definition)
    public function testAssertTagAttributesMultiPartClassFalse()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertNoTag(array('tag' => 'div',
                                 'id'  => 'test_multi_class',
                                 'attributes' => array('class' => 'mul')));
    }
   
    // match a node's parent
    public function testAssertTagParentTrue()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('tag'    => 'head',
                               'parent' => array('tag' => 'html')));
    }
   
    // match a node's parent
    public function testAssertTagParentFalse()
    {
        $this->get('/unit_test/test_assert_tag');
        $e = null;
        try {
            $this->assertTag(array('tag'    => 'head',
                                   'parent' => array('tag' => 'div')));
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // match at least one of the node's immediate children
    public function testAssertTagChildTrue()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('tag'   => 'html',
                               'child' => array('tag' => 'head')));
    }
   
    // match at least one of the node's immediate children
    public function testAssertTagChildFalse()
    {
        $this->get('/unit_test/test_assert_tag');
        $e = null;
        try {
            $this->assertTag(array('tag'   => 'html',
                                   'child' => array('tag' => 'div')));
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // match at least one of the node's ancestors
    public function testAssertTagAncestorTrue()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('tag'      => 'div',
                               'ancestor' => array('tag' => 'html')));
    }
   
    // match at least one of the node's ancestors
    public function testAssertTagAncestorFalse()
    {
        $this->get('/unit_test/test_assert_tag');
        $e = null;
        try {
            $this->assertTag(array('tag'      => 'html',
                                   'ancestor' => array('tag' => 'div')));
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // must match at least one of the node's descendants
    public function testAssertTagDescendantTrue()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('tag'        => 'html',
                               'descendant' => array('tag' => 'div')));
   }
   
    // must match at least one of the node's descendants
    public function testAssertTagDescendantFalse()
    {
        $this->get('/unit_test/test_assert_tag');
        $e = null;
        try {
            $this->assertTag(array('tag'        => 'div',
                                   'descendant' => array('tag' => 'html')));
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // a number of children to of a node
    public function testAssertTagChildrenCountTrue()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('tag'      => 'ul',
                               'children' => array('count' => 3)
                         ));
    }
   
    // a number of children to of a node
    public function testAssertTagChildrenCountFalse()
    {
        $this->get('/unit_test/test_assert_tag');
        $e = null;
        try {
            $this->assertTag(array('tag'      => 'ul',
                                   'children' => array('count' => 5)
                             ));
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // the number of children must be less this number
    public function testAssertTagChildrenLessThanTrue()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('tag'      => 'ul',
                               'children' => array('less_than' => 10)
                         ));
    }
   
    // the number of children must be less this number
    public function testAssertTagChildrenLessThanFalse()
    {
        $this->get('/unit_test/test_assert_tag');
        $e = null;
        try {
            $this->assertTag(array('tag'      => 'ul',
                                   'children' => array('less_than' => 2)
                             ));
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // the number of children must be greater than this number
    public function testAssertTagChildrenGreaterThanTrue()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('tag'      => 'ul',
                               'children' => array('greater_than' => 2)
                         ));
    }
   
    // the number of children must be greater than this number
    public function testAssertTagChildrenGreaterThanFalse()
    {
        $this->get('/unit_test/test_assert_tag');
        $e = null;
        try {
            $this->assertTag(array('tag'      => 'ul',
                                   'children' => array('greater_than' => 10)
                             ));
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // make sure that the children are only of a certain tag
    public function testAssertTagChildrenOnlyTrue()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('tag'      => 'ul',
                               'children' => array('only' => array('tag' =>'li'))));
    }
   
    // make sure that the children are only of a certain tag
    public function testAssertTagChildrenOnlyFalse()
    {
        $this->get('/unit_test/test_assert_tag');
        $e = null;
        try {
            $this->assertTag(array('tag'      => 'ul',
                                   'children' => array('only' => array('tag' =>'div'))));
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
   
    /*##########################################################################
    # Combo Assert Tags
    ##########################################################################*/

    // check combinations of tag / id
    public function testAssertTagTypeIdTrueA()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('tag' => 'ul',
                               'id'  => 'my_ul'));
    }
   
    // check combinations of tag / id
    public function testAssertTagTypeIdTrueB()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('id'  => 'my_ul',
                               'tag' => 'ul'));
    }
   
    // check combinations of inptu tag / id
    public function testAssertTagTypeIdTrueC()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('tag' => 'input',
                               'id'  => 'input_test_id'));
    }
   
    // check combinations of tag / id
    public function testAssertTagTypeIdFalse()
    {
        $this->get('/unit_test/test_assert_tag');
        $e = null;
        try {
            $this->assertTag(array('tag' => 'div',
                                   'id'  => 'my_ul'));
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // check we can have content/attributes all set
    public function testAssertTagContentAttributes()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('tag'        => 'div',
                               'content'    => 'Test Id Text',
                               'attributes' => array('id'    => 'test_id',
                                                     'class' => 'my_test_class')));
    }
   
    // check we can have parent/content/attributes all set
    public function testAssertParentContentAttributes()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('tag'        => 'div',
                               'content'    => 'Test Id Text',
                               'attributes' => array('id'    => 'test_id',
                                                     'class' => 'my_test_class'),
                               'parent'     => array('tag' => 'body')));
    }
   
    // check we can have child/content/attributes all set
    public function testAssertChildContentAttributes()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('tag'        => 'div',
                               'content'    => 'Test Id Text',
                               'attributes' => array('id'    => 'test_id',
                                                     'class' => 'my_test_class'),
                               'child'      => array('tag'        => 'div',
                                                     'attributes' => array('id' => 'test_child_id'))
                        ));
    }
   
    // check we can have children with children
    public function testAssertChildSubChildren()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('id'    => 'test_id',
                               'child' => array('id' => 'test_child_id',
                                                'child' => array('id' => 'test_subchild_id'))
                         ));
    }
   
    // check we can have ancestor/content/attributes all set
    public function testAssertAncestorContentAttributes()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('id'         => 'test_subchild_id',
                               'content'    => 'My Subchild',
                               'attributes' => array('id' => 'test_subchild_id'),
                               'ancestor'   => array('tag'        => 'div',
                                                     'attributes' => array('id' => 'test_id'))
                        ));
    }
   
    // check we can have descendant/content/attributes all set
    public function testAssertDescendantContentAttributes()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('id'         => 'test_id',
                               'content'    => 'Test Id Text',
                               'attributes' => array('id'  => 'test_id'),
                               'descendant' => array('tag'        => 'span',
                                                     'attributes' => array('id' => 'test_subchild_id'))
                        ));
    }
   
    // check we can have children/content/attributes all set
    public function testAssertChildrenContentAttributes()
    {
        $this->get('/unit_test/test_assert_tag');
        $this->assertTag(array('id'         => 'test_children',
                               'content'    => 'My Children',
                               'attributes' => array('class'  => 'children'),
   
                               'children' => array('less_than'    => '25',
                                                   'greater_than' => '2',
                                                   'only'         => array('tag' => 'div',
                                                                           'attributes' => array('class' => 'my_child'))
                                                   )
                        ));
    }
   
   
    /*##########################################################################
    # Test Converting Assert Select
    ##########################################################################*/

    // complex selector using most options
    // 'div#folder.open a[href="http://www.xerox.com"][title="xerox"].selected.big > span
    public function testConvertAssertSelect()
    {
        $selector  = 'div#folder.open a[href="http://www.xerox.com"][title="xerox"].selected.big > span';
        $converted = $this->_convertSelectToTag($selector);
        $tag       = array('tag'   => 'div',
                           'id'    => 'folder',
                           'class' => 'open',
                           'descendant' => array('tag'        => 'a',
                                                 'class'      => 'selected big',
                                                 'attributes' => array('href'  => 'http://www.xerox.com',
                                                                       'title' => 'xerox'),
                                                 'child'      => array('tag' => 'span')));
         $this->assertEquals($tag, $converted);
    }
   
    // selected based only on element
    // 'div'
    public function testConvertAssertSelectElt()
    {
        $selector = 'div';
        $converted = $this->_convertSelectToTag($selector);
        $tag      = array('tag' => 'div');
   
        $this->assertEquals($tag, $converted);
    }
   
    // selector based on element class
    // '.foo'
    public function testConvertAssertClass()
    {
        $selector = '.foo';
        $converted = $this->_convertSelectToTag($selector);
        $tag      = array('class' => 'foo');
   
        $this->assertEquals($tag, $converted);
    }
   
    // selector based on element id
    // '#foo'
    public function testConvertAssertId()
    {
        $selector = '#foo';
        $converted = $this->_convertSelectToTag($selector);
        $tag      = array('id' => 'foo');
   
        $this->assertEquals($tag, $converted);
    }
   
    // selector based on element attribute value
    // '[foo="bar"]'
    public function testConvertAssertAttribute()
    {
        $selector = '[foo="bar"]';
        $converted = $this->_convertSelectToTag($selector);
        $tag      = array('attributes' => array('foo' => 'bar'));
   
        $this->assertEquals($tag, $converted);
    }
   
    // selector based on element attribute with spaces
    public function testConvertAssertAttributeSpaces()
    {
        $selector = '[foo="bar baz"] div[value="foo bar"]';
        $converted = $this->_convertSelectToTag($selector);
        $tag      = array('attributes' => array('foo' => 'bar baz'),
                          'descendant' => array('tag'        => 'div',
                                                'attributes' => array('value' => 'foo bar')));
        $this->assertEquals($tag, $converted);
    }
   
    // selector based on element and class
    // 'div.foo'
    public function testConvertAssertSelectEltClass()
    {
        $selector = 'div.foo';
        $converted = $this->_convertSelectToTag($selector);
        $tag      = array('tag' => 'div', 'class' => 'foo');
   
        $this->assertEquals($tag, $converted);
    }
   
    // selector based on element and id
    // 'div#foo'
    public function testConvertAssertSelectEltId()
    {
        $selector = 'div#foo';
        $converted = $this->_convertSelectToTag($selector);
        $tag      = array('tag' => 'div', 'id' => 'foo');
   
        $this->assertEquals($tag, $converted);
    }
   
    // selector based on element and attribute value
    // 'div[foo="bar"]'
    public function testConvertAssertSelectEltAttrEqual()
    {
        $selector = 'div[foo="bar"]';
        $converted = $this->_convertSelectToTag($selector);
        $tag      = array('tag' => 'div', 'attributes' => array('foo' => 'bar'));
   
        $this->assertEquals($tag, $converted);
    }
   
    // selector based on element and multiple attributes
    // 'div[foo="bar"][baz="fob"]'
    public function testConvertAssertSelectEltMultiAttrEqual()
    {
        $selector = 'div[foo="bar"][baz="fob"]';
        $converted = $this->_convertSelectToTag($selector);
        $tag      = array('tag' => 'div', 'attributes' => array('foo' => 'bar', 'baz' => 'fob'));
   
        $this->assertEquals($tag, $converted);
    }

    // selector based on element with attribute that contains the given text using word boundaries
    // 'div[foo~="bar"]'
    public function testConvertAssertSelectEltAttrHasOne()
    {
        $selector = 'div[foo~="bar"]';
        $converted = $this->_convertSelectToTag($selector);
        $tag      = array('tag' => 'div', 'attributes' => array('foo' => '/.*\bbar\b.*/'));
       
        $this->assertEquals($tag, $converted);
    }
       
    // selector based on element with attribute that contains the given text
    // 'div[foo*="bar"]'
    public function testConvertAssertSelectEltAttrContains()
    {
        $selector = 'div[foo*="bar"]';
        $converted = $this->_convertSelectToTag($selector);
        $tag      = array('tag' => 'div', 'attributes' => array('foo' => '/.*bar.*/'));
       
        $this->assertEquals($tag, $converted);
    }
    
    // selector based on element with a child element
    // 'div > a'
    public function testConvertAssertSelectEltChild()
    {
        $selector = 'div > a';
        $converted = $this->_convertSelectToTag($selector);
        $tag      = array('tag' => 'div', 'child' => array('tag' => 'a'));
   
        $this->assertEquals($tag, $converted);
    }
   
    // selector based on element with a descendant element
    // 'div a'
    public function testConvertAssertSelectEltDescendant()
    {
        $selector  = 'div a';
        $converted = $this->_convertSelectToTag($selector);
        $tag       = array('tag' => 'div', 'descendant' => array('tag' => 'a'));
   
        $this->assertEquals($tag, $converted);
    }
   
    // selector based on element with content
    // '#foo', 'div contents'
    public function testConvertAssertSelectContent()
    {
        $selector  = '#foo';
        $content   = 'div contents';
        $converted = $this->_convertSelectToTag($selector, $content);
        $tag       = array('id' => 'foo', 'content' => 'div contents');
   
        $this->assertEquals($tag, $converted);
    }
   
    // selector based on element that exists
    // '#foo', true
    public function testConvertAssertSelectTrue()
    {
        $selector  = '#foo';
        $content   = true;
        $converted = $this->_convertSelectToTag($selector, $content);
        $tag       = array('id' => 'foo');
   
        $this->assertEquals($tag, $converted);
    }
   
    // selector based on element that doesn't exist
    // '#foo', false
    public function testConvertAssertSelectFalse()
    {
        $selector  = '#foo';
        $content   = false;
        $converted = $this->_convertSelectToTag($selector, $content);
        $tag       = array('id' => 'foo');
   
        $this->assertEquals($tag, $converted);
    }
   
    // selector based on element class that appears 3 times
    // '.foo', '3'
    public function testConvertAssertNumber()
    {
        $selector  = '.foo';
        $content   = 3;
        $converted = $this->_convertSelectToTag($selector, $content);
        $tag       = array('class' => 'foo');
   
        $this->assertEquals($tag, $converted);
    }
   
    // selector based on element class that appears 6 to 9 times
    // '#foo', array('greater_than' => 5, 'less_than' => 10)
    public function testConvertAssertRange()
    {
        $selector  = '#foo';
        $content   = array('greater_than' => 5, 'less_than' => 10);
        $converted = $this->_convertSelectToTag($selector, $content);
        $tag       = array('id' => 'foo');
   
        $this->assertEquals($tag, $converted);
    }
   
    /*##########################################################################
     # Assert Select
     ##########################################################################*/

     // attempt to match element passes
     public function testAssertSelectPresentTrue()
     {
         $this->get('/unit_test/test_assert_tag');
         $selector = 'div#test_id';
         $content  = true;
    
         $this->assertSelect($selector, $content);
     }
    
     // attempt to match element fails
     public function testAssertSelectPresentFalse()
     {
         $this->get('/unit_test/test_assert_tag');
         $selector = 'div#non_existent';
         $content  = true;
    
         $e = null;
         try {
             $this->assertSelect($selector, $content);
         } catch (Exception $e) {}
         $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
     }
    
     // attempt to NOT match element passes
     public function testAssertSelectNotPresentTrue()
     {
         $this->get('/unit_test/test_assert_tag');
         $selector = 'div#non_existent';
         $content  = false;
    
         $this->assertSelect($selector, $content);
     }
    
    // attempt to NOT match element fails
    public function testAssertSelectNotPresentFalse()
    {
        $this->get('/unit_test/test_assert_tag');
        $selector = 'div#test_id';
        $content  = false;
   
        $e = null;
        try {
            $this->assertSelect($selector, $content);
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // attempt to match content passes
    public function testAssertSelectContentPresentTrue()
    {
        $this->get('/unit_test/test_assert_tag');
        $selector = 'span.test_class';
        $content  = 'Test Class Text';
   
        $this->assertSelect($selector, $content);
    }
   
    // attempt to match content fails
    public function testAssertSelectContentPresentFalse()
    {
        $this->get('/unit_test/test_assert_tag');
        $selector = 'span.test_class';
        $content  = 'Test Nonexistent';
   
        $e = null;
        try {
            $this->assertSelect($selector, $content);
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // attempt to match content passes
    public function testAssertSelectContentNotPresentTrue()
    {
        $this->get('/unit_test/test_assert_tag');
        $selector = 'span.test_class';
        $content  = 'Test Nonexistent';
   
        $this->assertSelect($selector, $content, false);
    }
   
    // attempt to match content fails
    public function testAssertSelectContentNotPresentFalse()
    {
        $this->get('/unit_test/test_assert_tag');
        $selector = 'span.test_class';
        $content  = 'Test Class Text';
   
        $e = null;
        try {
            $this->assertSelect($selector, $content, false);
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // attempt to match count passes
    public function testAssertSelectCountChildTrue()
    {
        $this->get('/unit_test/test_assert_tag');
        $selector = '#my_ul > li';
        $content  = 3;
   
        $this->assertSelect($selector, $content);
    }
   
    // attempt to match count fails
    public function testAssertSelectCountChildFalse()
    {
        $this->get('/unit_test/test_assert_tag');
        $selector = '#my_ul > li';
        $content  = 4;
   
        $e = null;
        try {
            $this->assertSelect($selector, $content);
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }

    // attempt to match count passes
    public function testAssertSelectCountDescendantTrue()
    {
        $this->get('/unit_test/test_assert_tag');
        $selector = '#my_ul li';
        $content  = 3;
   
        $this->assertSelect($selector, $content);
    }
   
    // attempt to match count fails
    public function testAssertSelectCountDescendantFalse()
    {
        $this->get('/unit_test/test_assert_tag');
        $selector = '#my_ul li';
        $content  = 4;
   
        $e = null;
        try {
            $this->assertSelect($selector, $content);
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }

    // attempt to match range passes
    public function testAssertSelectGreaterThanTrue()
    {
        $this->get('/unit_test/test_assert_tag');
        $selector = '#my_ul > li';
        $content  = array('>' => 2);
   
        $this->assertSelect($selector, $content);
    }
   
    // attempt to match range fails
    public function testAssertSelectGreaterThanFalse()
    {
        $this->get('/unit_test/test_assert_tag');
        $selector = '#my_ul > li';
        $content  = array('>' => 3);
   
        $e = null;
        try {
            $this->assertSelect($selector, $content);
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }

    // attempt to match range passes
    public function testAssertSelectGreaterThanEqualToTrue()
    {
        $this->get('/unit_test/test_assert_tag');
        $selector = '#my_ul > li';
        $content  = array('>=' => 3);
   
        $this->assertSelect($selector, $content);
    }
   
    // attempt to match range fails
    public function testAssertSelectGreaterThanEqualToFalse()
    {
        $this->get('/unit_test/test_assert_tag');
        $selector = '#my_ul > li';
        $content  = array('>=' => 4);

        $e = null;
        try {
            $this->assertSelect($selector, $content);
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }

    // attempt to match range passes
    public function testAssertSelectLessThanTrue()
    {
        $this->get('/unit_test/test_assert_tag');
        $selector = '#my_ul > li';
        $content  = array('<' => 4);
   
        $this->assertSelect($selector, $content);
    }
    
   
    // attempt to match range fails
    public function testAssertSelectLessThanFalse()
    {
        $this->get('/unit_test/test_assert_tag');
        $selector = '#my_ul > li';
        $content  = array('<' => 3);
   
        $e = null;
        try {
            $this->assertSelect($selector, $content);
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }

    // attempt to match range passes
    public function testAssertSelectLessThanEqualToTrue()
    {
        $this->get('/unit_test/test_assert_tag');
        $selector = '#my_ul > li';
        $content  = array('<=' => 3);
   
        $this->assertSelect($selector, $content);
    }

    // attempt to match range fails
    public function testAssertSelectLessThanEqualToFalse()
    {
        $this->get('/unit_test/test_assert_tag');
        $selector = '#my_ul > li';
        $content  = array('<=' => 2);
   
        $e = null;
        try {
            $this->assertSelect($selector, $content);
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    // attempt to match range passes
    public function testAssertSelectRangeTrue()
    {
        $this->get('/unit_test/test_assert_tag');
        $selector = '#my_ul > li';
        $content  = array('>' => 2, '<' => 4);
   
        $this->assertSelect($selector, $content);
    }
   
    // attempt to match range fails
    public function testAssertSelectRangeFalse()
    {
        $this->get('/unit_test/test_assert_tag');
        $selector = '#my_ul > li';
        $content  = array('>' => 1, '<' => 3);
   
        $e = null;
        try {
            $this->assertSelect($selector, $content);
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    /*##########################################################################
    # Assertion of sent files
    ##########################################################################*/

    // make sure that file sent assertions work
    public function testAssertFileSentTrue()
    {
        $this->get('/unit_test/test_send_file_action_attach');
        $this->assertFileSent();
    }
   
    // make sure that file sent assertions work
    public function testAssertFileSentFalse()
    {
        $this->get('/unit_test/test_render_nothing');
   
        $e = null;
        try {
            $this->assertFileSent();
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
   
    // make sure that file sent assertions work
    public function testAssertFileSentDispositionTrue()
    {
        $this->get('/unit_test/test_send_file_action_inline');
        $this->assertFileSent(array('disposition' => 'inline'));
    }
   
    // make sure that file sent assertions work
    public function testAssertFileSentDispositionFalse()
    {
        $this->get('/unit_test/test_send_file_action_inline');
   
        $e = null;
        try {
            $this->assertFileSent(array('disposition' => 'attachment'));
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
   
    // make sure that file sent assertions work
    public function testAssertFileSentFilenameTrue()
    {
        $this->get('/unit_test/test_send_file_action_inline');
        $this->assertFileSent(array('filename' => 'myImg.jpg'));
   
    }
   
    // make sure that file sent assertions work
    public function testAssertFileSentFilenameFalse()
    {
        $this->get('/unit_test/test_send_file_action_inline');
   
        $e = null;
        try {
            $this->assertFileSent(array('filename' => 'test.jpg'));
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
   
    // make sure that file sent assertions work
    public function testAssertFileSentTypeTrue()
    {
        $this->get('/unit_test/test_send_file_action_inline');
        $this->assertFileSent(array('type' => 'image/jpeg'));
   
    }
   
    // make sure that file sent assertions work
    public function testAssertFileSentTypeFalse()
    {
        $this->get('/unit_test/test_send_file_action_inline');
   
        $e = null;
        try {
            $this->assertFileSent(array('type' => 'image/gif'));
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }
   
    /*##########################################################################
    ##########################################################################*/
}
