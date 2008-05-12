<?php
/**
 * @category   Mad
 * @package    Mad_View
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    Proprietary and Confidential 
 */

/**
 * Set environment
 */
if (!defined('MAD_ENV')) define('MAD_ENV', 'test');
if (!defined('MAD_ROOT')) {
    require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config/environment.php';
}

/**
 * @group      view
 * @category   Mad
 * @package    Mad_View
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    Proprietary and Confidential
 */
class Mad_View_Helper_BenchmarkTest extends Mad_Test_Unit
{
    public function setUp()
    {
        $this->view = new Mad_View_Base();
        $this->view->addHelper(new Mad_View_Helper_Benchmark($this->view));

        $log = new Zend_Log($this->mock = new Zend_Log_Writer_Mock);
        $this->view->logger = $log;
    }
    
    public function testWithoutLogger()
    {
        $this->view = new Mad_View_Base();
        $this->view->addHelper(new Mad_View_Helper_Benchmark($this->view));

        $bench = $this->view->benchmark();
        $bench->end();
    }

    public function testDefaults()
    {
        $bench = $this->view->benchmark();
        $bench->end();
        $this->assertEquals(1, count($this->mock->events));
        $this->assertLastLogged();
    }

    public function testWithMessage()
    {
        $bench = $this->view->benchmark('test_run');
        $bench->end();
        $this->assertEquals(1, count($this->mock->events));
        $this->assertLastLogged('test_run');
    }

    public function testWithMessageAndLevelAsString()
    {
        $bench = $this->view->benchmark('debug_run', 'debug');
        $bench->end();
        $this->assertEquals(1, count($this->mock->events));
        $this->assertLastLogged('debug_run', 'debug');
    }

    public function testWithMessageAndLevelAsInteger()
    {
        $bench = $this->view->benchmark('debug_run', Zend_Log::DEBUG);
        $bench->end();
        $this->assertEquals(1, count($this->mock->events));
        $this->assertLastLogged('debug_run', 'debug');
    }


    public function assertLastLogged($message = 'Benchmarking', $level = 'info')
    {
        $last = end($this->mock->events);
        $this->assertEquals(strtoupper($level), $last['priorityName']);
        $this->assertRegExp("/^$message \(.*\)$/", $last['message']);
    }
}
