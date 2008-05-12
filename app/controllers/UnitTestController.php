<?php

class UnitTestController extends ApplicationController
{
    protected $_executedBefore        = false;
    protected $_executedAfter         = false;
    protected $_executedBeforeExcept  = false;
    protected $_executedBeforeOnly    = false;
    protected $_executedBeforeAnother = false;

    protected $_executedSkippedBefore       = false;
    protected $_executedSkippedBeforeExcept = false;
    protected $_executedSkippedBeforeOnly   = false;

    protected $_executedAction        = false;
    protected $_executedMethodMissing = false;

    protected $_testIsGet             = false;
    protected $_testIsPost            = false;

    protected $_testMethod            = null;

    protected $_testCookie            = null;
    protected $_testSession           = null;
    protected $_testFlash             = null;
    protected $_testUsesLayout        = null;

    protected $_testGet               = null;
    protected $_testPost              = null;
    protected $_testFiles             = null;
    protected $_testParams            = null;
    protected $_testParamsAll         = null;


    protected function _initialize()
    {
        // add test filters for before/after action
        $this->beforeFilter('_myBeforeFilter');
        $this->afterFilter('_myAfterFilter');
        $this->beforeFilter('_myBeforeFilterExcept',
                            array('except' => array('UnitTestController::beforeFilterExcept2',
                                                    'beforeFilterExcept')));
        $this->beforeFilter('_myBeforeFilterOnly',
                            array('only' => array('UnitTestController::beforeFilterOnly2',
                                                  'beforeFilterOnly')));
        $this->beforeFilter('_myBeforeFilterAnother');


        // add filters that we'll skip
        $this->beforeFilter('_mySkippedBeforeFilter');
        $this->beforeFilter('_mySkippedBeforeFilterExcept');
        $this->beforeFilter('_mySkippedBeforeFilterOnly');

        // skip some filters
        $this->skipBeforeFilter('_mySkippedBeforeFilter');
        $this->skipBeforeFilter('_mySkippedBeforeFilterExcept', 
                                array('except' => array('UnitTestController::skipBeforeFilterExcept2', 
                                                        'skipBeforeFilterExcept')));
        $this->skipBeforeFilter('_mySkippedBeforeFilterOnly', 
                                array('only' => array('UnitTestController::skipBeforeFilterOnly2', 
                                                      'skipBeforeFilterOnly')));
    }


    /*##########################################################################
    # Test View
    ##########################################################################*/

    // test default template
    public function testView()
    {
        // view must receive controller instance for helpers
        if ($this->_view->controller !== $this) {
            throw new Exception('view did not receive controller instance');
        }

        // view must be initialized with builtin helpers
        if ($this->_view->tag('foo') != '<foo />') {
            throw new Exception('view was not initialized with built-in helpers');
        }

        $this->useLayout(false);
        $this->_view->testVar = 'test';
    }

    // test using layout
    public function testViewLayout()
    {
        $this->setLayout('testLayout');

        $this->_view->testVar = 'test1';
        $this->renderAction('testView');
    }

    // test using partial
    public function testViewPartial()
    {
        $this->useLayout(false);
        $this->_view->testVar = 'test2';
    }

    // test using partial in subdir
    public function testViewPartialInSubdir()
    {
        $this->useLayout(false);
        $this->_view->testVar = 'test2';
    }

    // test that default helper is loaded & has access to vars
    public function testDefaultHelper()
    {
        $this->useLayout(false);
    }

    // test that other helper is loaded
    public function testAddHelper()
    {
        $this->useLayout(false);
        $this->helper('SpecialTest');
    }


    /*##########################################################################
    # Test Action Methods
    ##########################################################################*/

    // test action
    public function testAction()
    {
        $this->setLayout('application');
        
        $this->_view->testVariable = "buga buga";

        // set a cookie/session/flash to test function test retrieval of them
        $this->cookie['functional_cookie'] = 'test cookie data';
        $this->session['functional_session'] = 'test session data';
        $this->flash['functional_flash'] = 'test flash data';

        $this->_executedAction = true;
        $this->_testParams = $this->params->getArrayCopy();

        $this->_testMethod = $this->_request->getMethod();
    }

    // test if isGet
    public function testRequestMethod()
    {
        if ($this->isGet()) {
            $this->_testIsGet = true;
        }
        if ($this->isPost()) {
            $this->_testIsPost = true;
        }
        $this->render(array('nothing' => true));
    }

    // test redirection action
    public function testRedirectAction()
    {
        $this->redirectTo('/unit_test/test_action/123');
    }

    // test respond to
    public function testRespondTo()
    {
        $wants = $this->respondTo();
        if ($wants->html) { $this->renderText('html'); }
        if ($wants->js)   { $this->renderText('js'); }
    }

    // test sendFile action
    public function testSendFileActionAttach()
    {
        // send example zip
        $this->sendFile(MAD_ROOT.'/test/test.txt');
    }

    // test sendFile action on jpg
    public function testSendFileActionInline()
    {
        // send example zip
        $this->sendFile(MAD_ROOT.'/test/test.txt', array('filename'    => 'myImg.jpg',
                                                          'type'        => 'image/jpeg',
                                                          'disposition' => 'inline'));
    }

    // test sendData action
    public function testSendDataActionAttach()
    {
        // send example text file
        $this->sendData('my data', array('filename' => 'MyData.txt'));
    }

    // test sendData action
    public function testSendDataActionInline()
    {
        // send example text file
        $this->sendData('my data', array('filename'    => 'BriefcaseReport.csv',
                                         'type'        => 'application/ms-excel',
                                         'disposition' => 'inline'));
    }

    // test setting a layout
    public function testSetLayout()
    {
        $this->setLayout('application');
        $this->render(array('action' => 'testAction'));
    }

    // test using a layout
    public function testUseLayout()
    {
        $this->setLayout('application');
        $this->useLayout(false);

        $this->_testUsesLayout = $this->usesLayout();

        $this->render(array('action' => 'testAction'));
    }

    // test setting param data in action
    public function testParamData()
    {
        $this->_testParams    = $this->params->get('id', 'default');
        $this->_testParamsAll = $this->params->getArrayCopy();

        $this->render(array('nothing' => true));
    }
    
    // test getting get data
    public function testGetData()
    {
        $this->_testGet = $this->params->get('name', 'default');

        $this->render(array('nothing' => true));
    }

    // test getting post data
    public function testPostData()
    {
        $this->_testPost = $this->params->get('name', 'default');

        $this->render(array('nothing' => true));
    }

    // test getting files data
    public function testFilesData()
    {
        $this->_testFiles = $this->params->get('pictures', array());

        $this->render(array('nothing' => true));
    }
    
    // test setting session data in action
    public function testSetSessionData()
    {
        $this->cookie['MY TEST COOKIE'] = 'my test cookie';
        $this->session['MY TEST SESSION'] = 'my test session';
        $this->flash['MY TEST FLASH'] = 'my test flash';
        $this->flash->now('MY FLASH NOW',   'my flash now');

        $this->render(array('nothing' => true));
    }
    
    // test setting session data in action
    public function testResetSessionData()
    {
        $this->session->reset();

        $this->render(array('nothing' => true));
    }

    // test tag assertions
    public function testAssertTag()
    {
        $this->useLayout(false);
    }


    /*##########################################################################
    # Test Render Methods
    ##########################################################################*/

    public function testRenderStatus()
    {
        $this->render(array('text' => '403 Forbidden', 'status' => 403));
    }

    // test rendering some text
    public function testRenderText()
    {
        $this->render(array('text' => 'some sample text'));
    }

    // test rendering a template that is not the default for this action
    public function testRenderAction()
    {
        $this->render(array('action' => 'testAction'));
    }

    // test rendering nothing
    public function testRenderNothing()
    {
        $this->render(array('nothing' => true));
    }

    // test function conflict with layout file
    public function error()
    {
        $this->useLayout(false);

        $this->render(array('action' => 'error'));
    }


    /*##########################################################################
    # Method missing test
    ##########################################################################*/

    public function methodMissing()
    {
        $this->_executedMethodMissing = true;

        $this->render(array('nothing' => true));
    }


    /*##########################################################################
    # Test Filter Methods
    ##########################################################################*/

    public function beforeFilterExcept()
    {
        $this->render(array('nothing' => true));
    }

    public function beforeFilterExcept2()
    {
        $this->render(array('nothing' => true));
    }

    public function beforeFilterOnly()
    {
        $this->render(array('nothing' => true));
    }

    public function beforeFilterOnly2()
    {
        $this->render(array('nothing' => true));
    }

    public function beforeFilterAnother()
    {
        $this->render(array('nothing' => true));
    }


    public function skipBeforeFilterExecution()
    {
        $this->render(array('nothing' => true));
    }

    public function skipBeforeFilterOnly()
    {
        $this->render(array('nothing' => true));
    }

    public function skipBeforeFilterOnly2()
    {
        $this->render(array('nothing' => true));
    }

    public function skipBeforeFilterExcept()
    {
        $this->render(array('nothing' => true));
    }

    public function skipBeforeFilterExcept2()
    {
        $this->render(array('nothing' => true));
    }


    protected function _myBeforeFilter()
    {
        $this->_executedBefore = true;
    }

    protected function _myAfterFilter()
    {
        $this->_executedAfter = true;
    }

    protected function _myBeforeFilterExcept()
    {
        $this->_executedBeforeExcept = true;
    }

    protected function _myBeforeFilterOnly()
    {
        $this->_executedBeforeOnly = true;
    }

    protected function _myBeforeFilterAnother()
    {
        $this->_executedBeforeAnother = true;
    }


    protected function _mySkippedBeforeFilter()
    {
        $this->_executedSkippedBefore = true;
    }

    protected function _mySkippedBeforeFilterExcept()
    {
        $this->_executedSkippedBeforeExcept = true;
    }

    protected function _mySkippedBeforeFilterOnly()
    {
        $this->_executedSkippedBeforeOnly = true;
    }
}

?>