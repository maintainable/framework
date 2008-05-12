<?php
/**
 * @category   Mad
 * @package    Mad_Support
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    Proprietary and Confidential 
 */

/**
 * Set environment
 */
if (!defined('MAD_ENV')) define('MAD_ENV', 'test');
if (!defined('MAD_ROOT')) {
    require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config/environment.php';
}

/**
 * @group      support
 * @category   Mad
 * @package    Mad_Support
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    Proprietary and Confidential
 */
class Mad_Support_BaseTest extends Mad_Test_Unit
{
    // test validating keys
    public function testAssertValidKeysValidKeys()
    {
        $options = array('testA' => 1, 'testB' => 2, 'testC' => 3);
        $valid = array('testA', 'testB', 'testC');

        $expected = array('testA' => 1, 'testB' => 2, 'testC' => 3);
        $validated = Mad_Support_Base::assertValidKeys($options, $valid);
        $this->assertEquals($expected, $validated);
    }

    // test validating keys
    public function testAssertValidKeysValidKeysEmpty()
    {
        $options = array('testA' => 1, 'testB' => 2);
        $valid = array('testA', 'testB', 'testC');

        $expected = array('testA' => 1, 'testB' => 2, 'testC' => null);
        $validated = Mad_Support_Base::assertValidKeys($options, $valid);
        $this->assertEquals($expected, $validated);
    }

    // test validating keys with defaults
    public function testAssertValidKeysDefaultValuesA()
    {
        $options = array('testA' => 1, 'testB' => 2);
        $valid = array('testA' => 23, 'testB' => 24, 'testC' => 25);

        $expected = array('testA' => 1, 'testB' => 2, 'testC' => 25);
        $validated = Mad_Support_Base::assertValidKeys($options, $valid);
        $this->assertEquals($expected, $validated);
    }

    // test validating keys with defaults
    public function testAssertValidKeysDefaultValuesB()
    {
        $options = array();
        $valid = array('testA' => 23, 'testB' => 24, 'testC' => 25);

        $expected = array('testA' => 23, 'testB' => 24, 'testC' => 25);
        $validated = Mad_Support_Base::assertValidKeys($options, $valid);
        $this->assertEquals($expected, $validated);
    }

    // test validating keys
    public function testAssertValidKeysInvalidKey()
    {
        $options = array('testA' => 1, 'testB' => 2, 'testD' => 3);
        $valid = array('testA', 'testB', 'testC');

        try {
            $validated = Mad_Support_Base::assertValidKeys($options, $valid);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertEquals('Unknown key(s): testD', $e->getMessage());
        }
    }

    // test validating keys
    public function testAssertValidKeysInvalidKeys()
    {
        $options = array('testA' => 1, 'testD' => 2, 'testE' => 3);
        $valid = array('testA', 'testB', 'testC');

        try {
            $validated = Mad_Support_Base::assertValidKeys($options, $valid);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertEquals('Unknown key(s): testD, testE', $e->getMessage());
        }
    }
    
    public function testAssertValidKeysThrowsExceptionWhenTypeIsNotArray()
    {
        foreach (array(null, 'foo', 42) as $bad) {
            try {
                Mad_Support_Base::assertValidKeys($bad, array());
                $this->fail();
            } catch (InvalidArgumentException $e) {
                $this->assertRegExp('/expected array/i', $e->getMessage());
            }
        }
    }

    // @see String#chop
    public function testChop()
    {
        $actual   = Mad_Support_Base::chop("string\r\n");
        $expected = 'string';
        $this->assertSame($expected, $actual);

        $actual   = Mad_Support_Base::chop("string\n\r");
        $expected = "string\n";
        $this->assertSame($expected, $actual);

        $actual   = Mad_Support_Base::chop("string\n");
        $expected = 'string';
        $this->assertSame($expected, $actual);

        $actual   = Mad_Support_Base::chop("string");
        $expected = 'strin';
        $this->assertSame($expected, $actual);

        $actual   = Mad_Support_Base::chop(Mad_Support_Base::chop("x"));
        $expected = '';
        $this->assertSame($expected, $actual);
    }
    
    // @see String#chop!
    public function testChopToNull()
    {
        $actual   = Mad_Support_Base::chopToNull('');
        $expected = null;
        $this->assertSame($expected, $actual);

        $actual   = Mad_Support_Base::chopToNull('x');
        $expected = null;
        $this->assertSame($expected, $actual);
    }

}
