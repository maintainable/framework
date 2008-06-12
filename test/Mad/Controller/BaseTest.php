<?php
/**
 * @category   Mad
 * @package    Mad_Controller
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
 * Used for functional testing of controller classes
 *
 * @group      controller
 * @category   Mad
 * @package    Mad_Controller
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Controller_BaseTest extends Mad_Test_Functional
{
    // set up
    public function setUp()
    {
        // set up request/routes
        $this->request  = new Mad_Controller_Request_Mock();
        $this->response = new Mad_Controller_Response_Mock();
        
        $this->fixtures('articles');
    }

    /*##########################################################################
    # Test Public Methods
    ##########################################################################*/

    // test getting all attributes
    public function testGetAttributes()
    {
        $this->recognize('/unit_test/test_action/');

        $attributes = $this->controller->getAttributes();
        $this->assertTrue(array_key_exists('_request', $attributes));
    }

    // test getting assigned template vars
    public function testGetAssigns()
    {
        $this->get('/unit_test/test_action/');
        $this->assertEquals('buga buga', $this->controller->getAssigns('testVariable'));
    }

    // test getting the list of templates used to parse the page.
    public function testGetTemplates()
    {
        $this->get('/unit_test/test_action/');

        $expected = array(MAD_ROOT . '/app/views/UnitTest/testAction.html',
                          MAD_ROOT . '/app/views/layouts/application.html');
        $this->assertEquals($expected, $this->controller->getTemplates());
    }

    public function testGetRequest()
    {
        $this->get('/unit_test/test_action/');
        $this->assertSame($this->request, $this->controller->getRequest());
    }

    public function testGetControllerName()
    {
        $this->get('/unit_test/test_action/');
        $this->assertEquals('unit_test', $this->controller->getControllerName());
    }

    public function testGetActionName()
    {
        $this->get('/unit_test/test_action/');
        $this->assertEquals('test_action', $this->controller->getActionName());
    }

    public function testGetAppendActionName()
    {
        $this->get('/unit_test/test/');
        $this->assertResponse('success');

        $this->assertEquals('test', $this->controller->getActionName());
    }

    /*##########################################################################
    # Test Process Methods
    ##########################################################################*/

    // make sure params data populates from the request
    public function testInitParams()
    {
        $this->get('/unit_test/test_action/7');
        $attributes = $this->controller->getAttributes();

        $expected = array('controller'  => 'unit_test',
                          ':controller' => 'UnitTestController',
                          'action'      => 'test_action',
                          ':action'     => 'testAction',
                          'id'          => '7');
        $this->assertEquals($expected, $attributes['_testParams']);
    }

    // combined params/get data
    public function testInitParamsCombined()
    {
        $this->get('/unit_test/test_action/7', array('test' => 'true'));
        $attributes = $this->controller->getAttributes();

        $expected = array('controller'  => 'unit_test',
                          ':controller' => 'UnitTestController',
                          'action'      => 'test_action',
                          ':action'     => 'testAction',
                          'id'          => '7',
                          'test'        => 'true');
        $this->assertEquals($expected, $attributes['_testParams']);
    }

    // test executing the action
    public function testExecuteAction()
    {
        $this->get('/unit_test/test_action');
        $attributes = $this->controller->getAttributes();

        $this->assertTrue($attributes['_executedAction']);
    }

    // test that request object works
    public function testRequest()
    {
        $this->get('/unit_test/test_action');
        $attributes = $this->controller->getAttributes();

        $this->assertEquals('GET', $attributes['_testMethod']);
    }

    // test isGet
    public function testIsGet()
    {
        $this->get('/unit_test/test_request_method');
        $attributes = $this->controller->getAttributes();

        $this->assertTrue($attributes['_testIsGet']);
        $this->assertFalse($attributes['_testIsPost']);
    }

    // test isPost
    public function testIsPost()
    {
        $this->post('/unit_test/test_request_method');
        $attributes = $this->controller->getAttributes();

        $this->assertTrue($attributes['_testIsPost']);
        $this->assertFalse($attributes['_testIsGet']);
    }

    // test getting params
    public function testParams()
    {
        $this->get('/unit_test/test_param_data/1');

        $attributes = $this->controller->getAttributes();
        $this->assertEquals('1', $attributes['_testParams']);
    }

    public function testParamsDefault()
    {
        $this->get('/unit_test/test_param_data');

        $attributes = $this->controller->getAttributes();

        $this->assertEquals('default', $attributes['_testParams']);
    }

    public function testParamsAll()
    {
        $this->get('/unit_test/test_param_data/1');

        $attributes = $this->controller->getAttributes();
        $expected = array('controller'  => 'unit_test',
                          ':controller' => 'UnitTestController',
                          'action'      => 'test_param_data',
                          ':action'     => 'testParamData',
                          'id'          => '1');
        $this->assertEquals($expected, $attributes['_testParamsAll']);
    }

    // test getting get params
    public function testGet()
    {
        $this->get('/unit_test/test_get_data', array('name' => 'abc'));

        $attributes = $this->controller->getAttributes();
        $this->assertEquals('abc', $attributes['_testGet']);
    }

    // test getting get params
    public function testGetDefault()
    {
        $this->get('/unit_test/test_get_data');

        $attributes = $this->controller->getAttributes();
        $this->assertEquals('default', $attributes['_testGet']);
    }

    // test getting post params
    public function testPost()
    {
        $this->post('/unit_test/test_post_data', array('name' => 'abc'));

        $attributes = $this->controller->getAttributes();
        $this->assertEquals('abc', $attributes['_testPost']);
    }

    // test getting post params
    public function testPostDefault()
    {
        $this->post('/unit_test/test_post_data');

        $attributes = $this->controller->getAttributes();
        $this->assertEquals('default', $attributes['_testPost']);
    }

    // test getting files params
    public function testFiles()
    {
        // set files array for request
        $this->files = array('pictures' => array('name'     => 'my_picture.gif',
                                                 'type'     => 'image/gif',
                                                 'size'     => '1234567',
                                                 'tmp_name' => 'my_tmp'));
        $this->post('/unit_test/test_files_data');
        $attributes = $this->controller->getAttributes();

        $expected = array('name'     => 'my_picture.gif',
                          'type'     => 'image/gif',
                          'size'     => '1234567',
                          'tmp_name' => 'my_tmp');
        $this->assertEquals($expected, $attributes['_testFiles']);
    }

    // test getting files params
    public function testFilesDefault()
    {
        $this->post('/unit_test/test_files_data');
        $attributes = $this->controller->getAttributes();

        $this->assertEquals(array(), $attributes['_testFiles']);
    }

    public function testMethodMissing()
    {
        $this->get('/unit_test/test_invalid_method');

        $attributes = $this->controller->getAttributes();
        $this->assertTrue($attributes['_executedMethodMissing']);
    }


    /*##########################################################################
    # Test Cookie/Session Data
    ##########################################################################*/

    public function testSetCookie()
    {
        $this->get('/unit_test/test_set_session_data/123');

        $cookie = $this->response->getCookie('MY TEST COOKIE');
        $expected = array('value'      => 'my test cookie', 
                          'expiration' => '0', 
                          'path'       => '/');
        $this->assertEquals($expected, $cookie);
    }

    public function testSetSession()
    {
        $this->get('/unit_test/test_set_session_data/123');

        $session = $this->response->getSession('MY TEST SESSION');
        $this->assertEquals('my test session', $session);
    }

    public function testResetSession()
    {
        $this->get('/unit_test/test_set_session_data/123');
        $session = $this->response->getSession('MY TEST SESSION');
        $this->assertEquals('my test session', $session);

        $this->get('/unit_test/test_reset_session_data/123');
        $session = $this->response->getSession('MY TEST SESSION');
        $this->assertNull($session);
    }

    public function testSetFlash()
    {
        $this->get('/unit_test/test_set_session_data/123');

        // flash tacks onto response
        $flash = $this->response->getFlash('MY TEST FLASH');
        $this->assertEquals('my test flash', $flash);
    }

     public function testSetFlashNow()
     {
         $this->get('/unit_test/test_set_session_data/123');

         // flash now tacks onto current request
         $flashNow = $this->request->getFlash('MY FLASH NOW');
         $this->assertEquals('my flash now', $flashNow);
     }

    /*##########################################################################
    # Test Views
    ##########################################################################*/

    // test basic view
    public function testGetView()
    {
        $this->get('/unit_test/test_view');

        $expected = '<div>test view</div>';
        $this->assertEquals($expected, $this->response->getBody());
    }

    // test using layout with view
    public function testGetViewLayout()
    {
        $this->get('/unit_test/test_view_layout');

        $expected = "<html><head></head><body><div>test1 view</div></body></html>";
        $this->assertEquals($expected, $this->response->getBody());
    }

    // test rendering partials
    public function testGetViewPartial()
    {
        $this->get('/unit_test/test_view_partial');
        $expected = "<div>Partial Content view</div>";
        $this->assertEquals($expected, $this->response->getBody());
    }

    // test setting a partial template file
    public function testGetViewPartialInSubdir()
    {
        $this->get('/unit_test/test_view_partial_in_subdir');

        $expected = "<div>Partial Content view</div>";
        $this->assertEquals($expected, $this->response->getBody());
    }

    // test using default helper
    public function testDefaultHelper()
    {
        $this->get('/unit_test/test_default_helper');
        $expected = '<div>Helper Sanc</div>';
        $this->assertEquals($expected, $this->response->getBody());
    }
    
    public function testAddHelper()
    {
        $this->get('/unit_test/test_add_helper');

        $expected = '<div>Helper TEXT</div>';
        $this->assertEquals($expected, $this->response->getBody());
    }

    // make sure we render the view templates in the correct path order
    public function testRenderViewPathOrdering()
    {
        $this->get('/unit_test/error');
        $expected = '<div>test name conflict</div>';
        $this->assertEquals($expected, $this->response->getBody());
    }


    /*##########################################################################
    # Test Random methods available to subclasses
    ##########################################################################*/

    // test using a layout template
    public function testUseLayout()
    {
        $this->get('/unit_test/test_use_layout/');
        $attributes = $this->controller->getAttributes();
        $this->assertFalse($attributes['_testUsesLayout']);
    }

    // test setting a layout template
    public function testSetLayout()
    {
        $this->get('/unit_test/test_set_layout/');
        $this->assertTemplate('/app/views/layouts/application.html');
    }


    /*##########################################################################
    # Test Render/Redirect Methods
    ##########################################################################*/

    public function testRenderStatus()
    {
        $this->get('/unit_test/test_render_status');

        // verify headers
        $headers = $this->response->getHeaders();
        $this->assertTrue(isset($headers["HTTP/1.1 403 Forbidden"]));
        $this->assertEquals('go away', $this->response->getBody());
    }
    
    public function testRenderStatusFromString()
    {
        $this->get('/unit_test/test_render_status_from_string');

        // verify headers
        $headers = $this->response->getHeaders();
        $this->assertTrue(isset($headers["HTTP/1.1 422 Unprocessable Entity"]));
        $this->assertEquals('errors', $this->response->getBody());
    }

    // test render text using renderText
    public function testRenderTextA()
    {
        $this->get('/unit_test/test_render_text/');
        $this->assertEquals('some sample text', $this->response->getBody());
    }

    // test rendering with status and location
    public function testRenderTextWithStatusAndLocation()
    {
        $this->get('/unit_test/test_render_text_with_status_and_location/');

        $headers = $this->response->getHeaders();
        $this->assertTrue(isset($headers["HTTP/1.1 201 Created"]));
        $this->assertTrue(isset($headers['Location: /unit_test/testAction']));

        $this->assertEquals('some sample text', $this->response->getBody());
    }

    public function testRenderXmlString()
    {
        $this->get('/unit_test/test_render_xml_string');
        
        $headers = $this->response->getHeaders();
        $this->assertTrue(isset($headers['Content-Type: application/xml']));
        $this->assertEquals('<foo></foo>', $this->response->getBody());
    }

    public function testRenderXmlModel()
    {
        $this->get('/unit_test/test_render_xml_model');
        
        $headers = $this->response->getHeaders();
        $this->assertTrue(isset($headers['Content-Type: application/xml']));

        $model = Article::find(1);
        $this->assertEquals($model->toXml(), $this->response->getBody());
    }

    // test rendering the default template file
    public function testRenderDefaultTemplate()
    {
        $this->get('/unit_test/test_action');
        $this->assertResponseContains('Rendered test action template');
    }

    // test rendering a template
    public function testRenderAction()
    {
        $this->get('/unit_test/test_use_layout');
        $this->assertResponseContains('Rendered test action template');
    }

    // test rendering nothing
    public function testRenderNothing()
    {
        $this->get('/unit_test/test_render_nothing');
        $this->assertEquals('', $this->response->getBody());
    }

    // test an action that redirects
    public function testRedirectToPath()
    {
        $this->get('/unit_test/test_redirect_action');
        $this->assertRedirectedTo('/unit_test/test_action/123');
    }

    public function testRespondToHtml()
    {
        $this->get('/unit_test/test_respond_to');
        $this->assertEquals('html', $this->response->getBody());
    }

    public function testRespondToJs()
    {
        $this->xhr('/unit_test/test_respond_to');
        $this->assertEquals('js', $this->response->getBody());
    }

    // test sending data as attachment
    public function testSendDataAttach()
    {
        $this->get('/unit_test/test_send_data_action_attach');
        $this->assertResponse('success');

        // verify headers
        $headers = $this->response->getHeaders();

        $this->assertFalse(isset($headers["Cache-Control: no-store, no-cache, must-revalidate"]));
        $this->assertFalse(isset($headers["Cache-Control: post-check=0, pre-check=0"]));
        $this->assertFalse(isset($headers["Pragma: no-cache"]));

        $this->assertTrue(isset($headers["Expires: 0"]));
        $this->assertTrue(isset($headers["Content-Length: 7"]));
        $this->assertTrue(isset($headers["Content-Type: application/octet-stream"]));
        $this->assertTrue(isset($headers["Content-Disposition: attachment; filename=MyData.txt"]));
        $this->assertTrue(isset($headers["Content-Transfer-Encoding: binary"]));
    }

    // test sending data inline
    public function testSendDataInline()
    {
        $this->get('/unit_test/test_send_data_action_inline');
        $this->assertResponse('success');

        // verify headers
        $headers = $this->response->getHeaders();

        $this->assertTrue(isset($headers["Expires: 0"]));
        $this->assertTrue(isset($headers["Content-Length: 7"]));
        $this->assertTrue(isset($headers["Content-Type: application/ms-excel"]));
        $this->assertTrue(isset($headers["Content-Disposition: inline; filename=BriefcaseReport.csv"]));
        $this->assertTrue(isset($headers["Content-Transfer-Encoding: binary"]));
    }

    // test sending file as attachment
    public function testSendFileAttach()
    {
        $this->get('/unit_test/test_send_file_action_attach');
        $this->assertResponse('success');

        // verify headers
        $headers = $this->response->getHeaders();

        $this->assertFalse(isset($headers["Cache-Control: no-store, no-cache, must-revalidate"]));
        $this->assertFalse(isset($headers["Cache-Control: post-check=0, pre-check=0"]));
        $this->assertFalse(isset($headers["Pragma: no-cache"]));

        $this->assertTrue(isset($headers["Expires: 0"]));
        $this->assertTrue(isset($headers["Content-Length: 25"]));
        $this->assertTrue(isset($headers["Content-Type: application/octet-stream"]));
        $this->assertTrue(isset($headers["Content-Disposition: attachment; filename=test.txt"]));
        $this->assertTrue(isset($headers["Content-Transfer-Encoding: binary"]));
    }

    // test sending file
    public function testSendFileInline()
    {
        $this->get('/unit_test/test_send_file_action_inline');
        $this->assertResponse('success');

        // verify headers
        $headers = $this->response->getHeaders();

        $this->assertTrue(isset($headers["Expires: 0"]));
        $this->assertTrue(isset($headers["Content-Length: 25"]));
        $this->assertTrue(isset($headers["Content-Type: image/jpeg"]));
        $this->assertTrue(isset($headers["Content-Disposition: inline; filename=myImg.jpg"]));
        $this->assertTrue(isset($headers["Content-Transfer-Encoding: binary"]));
    }
    
    /*##########################################################################
    # Test Head Method
    ##########################################################################*/

    public function testHeadWithInteger()
    {
        $this->get('/unit_test/test_head_with_integer');

        $headers = $this->response->getHeaders();
        $this->assertTrue(isset($headers["HTTP/1.1 201 Created"]));
    }

    public function testHeadWithString()
    {
        $this->get('/unit_test/test_head_with_string');

        $headers = $this->response->getHeaders();
        $this->assertTrue(isset($headers["HTTP/1.1 201 Created"]));
    }

    public function testHeadWithOptionsOnly()
    {
        $this->get('/unit_test/test_head_with_options_only');

        $headers = $this->response->getHeaders();
        $this->assertTrue(isset($headers["HTTP/1.1 201 Created"]));
        $this->assertTrue(isset($headers["Location: http://foo"]));
    }
    
    public function testHeadWithStringAndOptions()
    {
        $this->get('/unit_test/test_head_with_string_and_options');

        $headers = $this->response->getHeaders();
        $this->assertTrue(isset($headers["HTTP/1.1 201 Created"]));
        $this->assertTrue(isset($headers["Location: http://foo"]));
    }

    /*##########################################################################
    # Test Filter Methods
    ##########################################################################*/

    // test getting all attributes
    public function testBeforeFilterExecuted()
    {
        $this->get('/unit_test/test_action/');
        $attributes = $this->controller->getAttributes();

        $this->assertTrue($attributes['_executedBefore']);
    }

    // test getting all attributes
    public function testBeforeFilterExceptA()
    {
        $this->get('/unit_test/test_action/');
        $attributes = $this->controller->getAttributes();

        $this->assertTrue($attributes['_executedBeforeExcept']);
    }

    // test getting all attributes
    public function testBeforeFilterExceptB()
    {
        $this->get('/unit_test/before_filter_except/');
        $attributes = $this->controller->getAttributes();

        $this->assertFalse($attributes['_executedBeforeExcept']);
    }

    // test getting all attributes
    public function testBeforeFilterExceptC()
    {
        $this->get('/unit_test/before_filter_except2/');
        $attributes = $this->controller->getAttributes();

        $this->assertFalse($attributes['_executedBeforeExcept']);
    }

    // test filter runs before action
    public function testBeforeFilterOnlyA()
    {
        $this->get('/unit_test/test_action/');
        $attributes = $this->controller->getAttributes();

        $this->assertFalse($attributes['_executedBeforeOnly']);
    }

    // test filter runs before action
    public function testBeforeFilterOnlyB()
    {
        $this->get('/unit_test/before_filter_only/');
        $attributes = $this->controller->getAttributes();

        $this->assertTrue($attributes['_executedBeforeOnly']);
        $this->assertTrue($attributes['_executedBeforeAnother']);
    }

    // test filter runs before action
    public function testBeforeFilterOnlyC()
    {
        $this->get('/unit_test/before_filter_only2/');
        $attributes = $this->controller->getAttributes();

        $this->assertTrue($attributes['_executedBeforeOnly']);
    }

    // test filter runs before action
    public function testBeforeFilterAnother()
    {
        $this->get('/unit_test/before_filter_another/');
        $attributes = $this->controller->getAttributes();

        $this->assertTrue($attributes['_executedBeforeAnother']);
    }

    // test filter runs after action
    public function testAfterFilter()
    {
        $this->get('/unit_test/test_action/');
        $attributes = $this->controller->getAttributes();

        $this->assertTrue($attributes['_executedAfter']);
    }

    // before filter _mySkippedBeforeFilter() is always skipped
    public function testSkipBeforeFilter()
    {
        $this->get('/unit_test/test_action/');
        $attributes = $this->controller->getAttributes();

        $this->assertFalse($attributes['_executedSkippedBefore']);
    }

    // _mySkippedBeforeFilterExcept() is skipped for all but skipBeforeFilterExcept2 && skipBeforeFilterExcept
    public function testSkipBeforeFilterExceptA()
    {
        $this->get('/unit_test/test_action/');
        $attributes = $this->controller->getAttributes();

        $this->assertFalse($attributes['_executedSkippedBeforeExcept']);
    }
    public function testSkipBeforeFilterExceptB()
    {
        $this->get('/unit_test/skip_before_filter_except/');
        $attributes = $this->controller->getAttributes();

        $this->assertTrue($attributes['_executedSkippedBeforeExcept']);
    }
    public function testSkipBeforeFilterExceptC()
    {
        $this->get('/unit_test/skip_before_filter_except2/');
        $attributes = $this->controller->getAttributes();

        $this->assertTrue($attributes['_executedSkippedBeforeExcept']);
    }

    // _mySkippedBeforeFilterOnly() is only skipped for skipBeforeFilterOnly2 && skipBeforeFilterOnly
    public function testSkipBeforeFilterOnlyA()
    {
        $this->get('/unit_test/test_action/');
        $attributes = $this->controller->getAttributes();

        $this->assertTrue($attributes['_executedSkippedBeforeOnly']);
    }
    public function testSkipBeforeFilterOnlyB()
    {
        $this->get('/unit_test/skip_before_filter_only/');
        $attributes = $this->controller->getAttributes();

        $this->assertFalse($attributes['_executedSkippedBeforeOnly']);
    }
    public function testSkipBeforeFilterOnlyC()
    {
        $this->get('/unit_test/skip_before_filter_only2/');
        $attributes = $this->controller->getAttributes();

        $this->assertFalse($attributes['_executedSkippedBeforeOnly']);
    }


    /*##########################################################################
    ##########################################################################*/
}
