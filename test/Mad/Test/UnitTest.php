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
 * @category   Mad
 * @package    Mad_Test
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Test_UnitTest extends Mad_Test_Unit
{

    /*##########################################################################
    # DB Methods
    ##########################################################################*/

    // test connecting to the database
    public function testConnect()
    {
        $this->assertTrue(Mad_Model_Base::isConnected());
    }

    // test disconnecting from the db
    public function testDisconnect()
    {
        $this->_connect();
        $this->assertTrue(Mad_Model_Base::isConnected());

        $this->_disconnect();
        $this->assertFalse(Mad_Model_Base::isConnected());
    }

    /*##########################################################################
    # Fixture Methods
    ##########################################################################*/

    // test loading a fixture
    public function testFixturesA()
    {
        $this->fixtures('unit_tests');
        $this->assertEquals('name a', $this->unit_tests('unit_test_1')->string_value);
    }

    // test loading a fixture
    public function testFixturesB()
    {
        $this->fixtures('unit_tests', 'unit_tests_more');
        $this->assertEquals('name c', $this->unit_tests('unit_test_3')->string_value);
        $this->assertEquals('name f', $this->unit_tests_more('unit_test_7')->string_value);
    }

    // test loading a fixture
    public function testFixturesC()
    {
        $this->fixtures('unit_tests_more');
        $this->assertEquals('name f', $this->unit_tests_more('unit_test_7')->string_value);
    }

    // test fixtures 'except' option
    public function testFixturesExcept()
    {
        $this->fixtures('unit_tests', array('except' => array('testFixturesExcept')));
        
        try {
            $this->unit_tests('unit_test_3');
        } catch (Exception $e) {}
        $this->assertType('Mad_Test_Exception', $e);
    }

    // test fixtures 'only' option
    public function testFixturesOnly()
    {
        $this->fixtures('unit_tests', array('only' => array('testFixturesOnly')));

        $this->assertEquals('name a', $this->unit_tests('unit_test_1')->string_value);
    }

    /*##########################################################################
    # Assertion Methods
    ##########################################################################*/

    public function testAssertDifferenceTrue()
    {
        $this->fixtures('unit_tests');

        $diff = $this->assertDifference('UnitTest::count()');
            UnitTest::create(array('string_value'  => 'foo bar', 
                                   'integer_value' => 12345, 
                                   'email_value'   => 'test@example.com'));
        $diff->end();
    }

    public function testAssertDifferenceFalse()
    {
        try {
            $diff = $this->assertDifference('UnitTest::count()');
            $diff->end();
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }

    public function testAssertNoDifferenceTrue()
    {
        $diff = $this->assertNoDifference('UnitTest::count()');
        $diff->end();
    }

    public function testAssertNoDifferenceFalse()
    {
        try {
            $diff = $this->assertNoDifference('UnitTest::count()');
                UnitTest::create(array('string_value'  => 'foo bar', 
                                       'integer_value' => 12345, 
                                       'email_value'   => 'test@example.com'));
            $diff->end();
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof PHPUnit_Framework_AssertionFailedError);
    }


    /*##########################################################################
    ##########################################################################*/
}