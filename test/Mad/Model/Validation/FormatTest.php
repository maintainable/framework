<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage UnitTests
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
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
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Model_Validation_FormatTest extends Mad_Test_Unit
{
    public function setUp()
    {
        $this->model = new UnitTest();
    }

    // validate invalid options
    public function testInvalidOptions()
    {
        $e = null;
        try {
            $options = array('invalid_option' => 'test');
            $validation = Mad_Model_Validation_Base::factory('format', 'string_value', $options);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertRegExp('/unknown key/i', $e->getMessage());
        }
    }

    public function testInvalidRegularExpression()
    {
        try {
            $options = array('with' => '/foo');
            $validator = Mad_Model_Validation_Base::factory('format', 'string_value', $options);
            $this->fail();
        } catch (UnexpectedValueException $e) {
            $this->assertRegExp('/bad regular expression/i', $e->getMessage());
        }
    }

    // validate alpha ctype
    public function testFormatAlphaTrue()
    {
        $options    = array('with' => '[alpha]');
        $validation = Mad_Model_Validation_Base::factory('format', 'string_value', $options);

        $this->model->string_value = 'test';
        $validation->validate('save', $this->model);
        $this->assertEquals(array(), $this->model->errors->on('string_value'));
    }

    // validate alpha ctype
    public function testFormatAlphaFalse()
    {
        $options    = array('with' => '[alpha]');
        $validation = Mad_Model_Validation_Base::factory('format', 'string_value', $options);

        $this->model->string_value = 'abc123';
        $validation->validate('save', $this->model);
        $this->assertEquals(array('is invalid'), $this->model->errors->on('string_value'));
    }

    // validate digit ctype
    public function testFormatDigitTrue()
    {
        $options    = array('with' => '[digit]');
        $validation = Mad_Model_Validation_Base::factory('format', 'string_value', $options);

        $this->model->string_value = '123';
        $validation->validate('save', $this->model);
        $this->assertEquals(array(), $this->model->errors->on('string_value'));
    }

    // validate digit ctype
    public function testFormatDigitFalse()
    {
        $options    = array('with' => '[digit]');
        $validation = Mad_Model_Validation_Base::factory('format', 'string_value', $options);

        $this->model->string_value = 'abc123';
        $validation->validate('save', $this->model);
        $this->assertEquals(array('is invalid'), $this->model->errors->on('string_value'));
    }

    // validate alnum ctype
    public function testFormatAlnumTrue()
    {
        $options    = array('with' => '[alnum]');
        $validation = Mad_Model_Validation_Base::factory('format', 'string_value', $options);

        $this->model->string_value = 'abc123';
        $validation->validate('save', $this->model);
        $this->assertEquals(array(), $this->model->errors->on('string_value'));
    }

    // validate alnum ctype
    public function testFormatAlnumFalse()
    {
        $options    = array('with' => '[alnum]');
        $validation = Mad_Model_Validation_Base::factory('format', 'string_value', $options);

        $this->model->string_value = '123&&&';
        $validation->validate('save', $this->model);
        $this->assertEquals(array('is invalid'), $this->model->errors->on('string_value'));
    }


    // validate with regexp
    public function testFormatRegexpTrue()
    {
        $options    = array('with' => '/test|me/');
        $validation = Mad_Model_Validation_Base::factory('format', 'string_value', $options);

        $this->model->string_value = 'test';
        $validation->validate('save', $this->model);
        $this->assertEquals(array(), $this->model->errors->on('string_value'));
    }

    // validate with regexp
    public function testFormatRegexpFalse()
    {
        $options    = array('with' => '/test|me/');
        $validation = Mad_Model_Validation_Base::factory('format', 'string_value', $options);

        $this->model->string_value = 'te';
        $validation->validate('save', $this->model);
        $this->assertEquals(array('is invalid'), $this->model->errors->on('string_value'));
    }


    // validate alpha ctype
    public function testFormatCustomMessage()
    {
        $options    = array('with' => '[alpha]', 'message' => 'has to be made of letters');
        $validation = Mad_Model_Validation_Base::factory('format', 'string_value', $options);

        $this->model->string_value = 'abc123';
        $validation->validate('save', $this->model);
        $this->assertEquals(array('has to be made of letters'), $this->model->errors->on('string_value'));
    }


    /*##########################################################################
    ##########################################################################*/
}