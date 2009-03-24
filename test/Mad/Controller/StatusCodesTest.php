<?php
/**
 * @category   Mad
 * @package    Mad_Controller
 * @subpackage UnitTests
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
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
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Controller_StatusCodesTest extends Mad_Test_Unit
{
    public function testStatusCodesPropertyIsInitializedByClass()
    {
        $this->assertType('array', Mad_Controller_StatusCodes::$statusCodes);
        $this->assertTrue(isset(Mad_Controller_StatusCodes::$statusCodes[200]));
    }

    public function testInterpretWithBadStatusTypeThrowsException()
    {
        try {
            Mad_Controller_StatusCodes::interpret(new stdClass);
        } catch (InvalidArgumentException $e) {
            $expected = '$status must be numeric or string, got object';
            $this->assertEquals($expected, $e->getMessage());
            return;
        }

        $this->fail();
    }

    public function testInterpretWithValidIntegerReturnsHeader()
    {
        $expected = "200 OK";
        $actual   = Mad_Controller_StatusCodes::interpret(200);
        $this->assertEquals($expected, $actual);
    }

    public function testInterpretWithValidNumericStringReturnsHeader()
    {
        $expected = "200 OK";
        $actual   = Mad_Controller_StatusCodes::interpret('200');
        $this->assertEquals($expected, $actual);
    }
    
    public function testInterpretWithInvalidIntegerThrowsException()
    {
        try {
            Mad_Controller_StatusCodes::interpret(999);
        } catch (InvalidArgumentException $e) {
            $this->assertEquals('Unknown status code: 999', $e->getMessage());
            return;
        }

        $this->fail();
    }

    public function testInterpretWithValidStringReturnsHeader()
    {
        $expected = "200 OK";
        $actual   = Mad_Controller_StatusCodes::interpret('ok');
        $this->assertEquals($expected, $actual);        
    }

    public function testInterpretWithValidCamelizedStringReturnsHeader()
    {
        $expected = "422 Unprocessable Entity";
        $actual   = Mad_Controller_StatusCodes::interpret('unprocessableEntity');
        $this->assertEquals($expected, $actual);        
    }

    public function testInterpretWithInvalidStringThrowsException()
    {
        try {
            Mad_Controller_StatusCodes::interpret('bl_ah');
        } catch (InvalidArgumentException $e) {
            $this->assertEquals("Unknown status: 'bl_ah'", $e->getMessage());
            return;
        }

        $this->fail();
    }

    public function testInterpretWithInvalidStringButValidIfCamelizedThrowsAndGivesHelp()
    {
        try {
            Mad_Controller_StatusCodes::interpret('unprocessable_entity');
        } catch (InvalidArgumentException $e) {
            $msg = "Unknown status: 'unprocessable_entity' (underscore), "
                 . "did you mean 'unprocessableEntity' (camel)?";
            $this->assertEquals($msg, $e->getMessage());
            return;
        }

        $this->fail();
    }

}