<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage UnitTests
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD 
 */

/**
 * Set environment
 */
if (!defined('MAD_ENV')) define('MAD_ENV', 'test');
if (!defined('MAD_ROOT')) {
    require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config/environment.php';
}

/**
 * @group      model
 * @category   Mad
 * @package    Mad_Model
 * @subpackage UnitTests
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Model_Validation_BaseTest extends Mad_Test_Unit
{

    // test creating a format
    public function testFactoryFormat()
    {
        $validation = Mad_Model_Validation_Base::factory('format', '', array('with' => '//'));
        $this->assertTrue($validation instanceof Mad_Model_Validation_Format);
    }

    // test creating a length
    public function testFactoryLength()
    {
        $validation = Mad_Model_Validation_Base::factory('length', '', array());
        $this->assertTrue($validation instanceof Mad_Model_Validation_Length);
    }

    // test creating a numericality
    public function testFactoryNumericality()
    {
        $validation = Mad_Model_Validation_Base::factory('numericality', '', array());
        $this->assertTrue($validation instanceof Mad_Model_Validation_Numericality);
    }

    // test creating a presence
    public function testFactoryPresence()
    {
        $validation = Mad_Model_Validation_Base::factory('presence', '', array());
        $this->assertTrue($validation instanceof Mad_Model_Validation_Presence);
    }

    // test creating a uniqueness
    public function testFactoryUniqueness()
    {
        $validation = Mad_Model_Validation_Base::factory('uniqueness', '', array());
        $this->assertTrue($validation instanceof Mad_Model_Validation_Uniqueness);
    }


    // test format validation
    public function testFormatValidation()
    {
        $this->fixtures('unit_tests');
        $test = UnitTest::find(1);
        $test->string_value = '&asdf*';

        $this->assertFalse($test->isValid());
        $this->assertTrue($test->errors->isInvalid('string_value'));
        $this->assertEquals(1, count($test->errors->on('string_value')));

        $this->assertFalse($test->save());
    }

    // test length validation
    public function testLengthValidation()
    {
        $this->fixtures('unit_tests');
        $test = UnitTest::find(1);
        $test->integer_value = 1234567;

        $this->assertFalse($test->isValid());
        $this->assertTrue($test->errors->isInvalid('integer_value'));
        $this->assertEquals(1, count($test->errors->on('integer_value')));

        $this->assertFalse($test->save());
    }

    // test numericality validation
    public function testNumericalityValidation()
    {
        $this->fixtures('unit_tests');
        $test = UnitTest::find(1);
        $test->integer_value = 'asdf';

        $this->assertFalse($test->isValid());
        $this->assertTrue($test->errors->isInvalid('integer_value'));
        $this->assertEquals(1, count($test->errors->on('integer_value')));

        $this->assertFalse($test->save());
    }

    // test presence validation
    public function testPresenceValidation()
    {
        $this->fixtures('unit_tests');
        $test = UnitTest::find(1);
        $test->integer_value = '';

        $this->assertFalse($test->isValid());
        $this->assertTrue($test->errors->isInvalid('integer_value'));
        $this->assertEquals(3, count($test->errors->on('integer_value')));

        $this->assertFalse($test->save());
    }

    // test uniqueness validation
    public function testUniquenessValidation()
    {
        $this->fixtures('unit_tests');
        $test = UnitTest::find(1);
        $test->integer_value = '2';

        $this->assertFalse($test->isValid());
        $this->assertTrue($test->errors->isInvalid('integer_value'));
        $this->assertEquals(1, count($test->errors->on('integer_value')));

        $this->assertFalse($test->save());
    }
    
    public function testEmailValidation() 
    {
        $this->fixtures('unit_tests');
        $test = UnitTest::find(1);
        $test->email_value = 'asdf';

        $this->assertFalse($test->isValid());
        $this->assertTrue($test->errors->isInvalid('email_value'));
        $this->assertEquals(1, count($test->errors->on('email_value')));

        $this->assertFalse($test->save());
    }

    /*##########################################################################
    ##########################################################################*/
}